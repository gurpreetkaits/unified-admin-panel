<?php

namespace App\Http\Controllers;

use App\Enums\ProjectRole;
use App\Http\Requests\InviteTeamMemberRequest;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    public function index(Project $project): Response
    {
        Gate::authorize('view', $project);

        return Inertia::render('projects/team', [
            'project' => $project,
            'members' => $project->members()
                ->with('user:id,name,email')
                ->whereNotNull('accepted_at')
                ->get()
                ->map(fn (ProjectMember $member) => [
                    'id' => $member->id,
                    'user' => $member->user,
                    'role' => $member->role->value,
                    'role_label' => $member->role->label(),
                    'accepted_at' => $member->accepted_at,
                ]),
            'owner' => $project->user()->select('id', 'name', 'email')->first(),
            'pendingInvitations' => $project->pendingInvitations()
                ->with('additionalProjects:id,name')
                ->get()
                ->map(fn (ProjectInvitation $invitation) => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'expires_at' => $invitation->expires_at,
                    'additional_projects' => $invitation->additionalProjects->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                ]),
            'roles' => collect(ProjectRole::assignable())
                ->map(fn (ProjectRole $role) => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ]),
            'canManageTeam' => request()->user()?->can('manageTeam', $project) ?? false,
            'availableProjects' => request()->user()
                ?->projects()
                ->where('id', '!=', $project->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get() ?? [],
        ]);
    }

    public function invite(InviteTeamMemberRequest $request, Project $project): RedirectResponse
    {
        $email = $request->validated('email');
        $role = ProjectRole::from($request->validated('role'));
        $additionalProjectIds = $request->validated('additional_project_ids', []);

        // Check if user is already a member
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $project->hasMember($existingUser)) {
            return back()->withErrors(['email' => 'This user is already a team member.']);
        }

        // Verify current user owns all additional projects
        if (! empty($additionalProjectIds)) {
            $ownedProjectIds = $request->user()->projects()->pluck('id')->toArray();
            $invalidProjects = array_diff($additionalProjectIds, $ownedProjectIds);
            if (! empty($invalidProjects)) {
                return back()->withErrors(['additional_project_ids' => 'You can only grant access to projects you own.']);
            }
        }

        // Check for existing pending invitation
        $existingInvitation = $project->pendingInvitations()
            ->where('email', $email)
            ->first();

        if ($existingInvitation) {
            return back()->withErrors(['email' => 'An invitation has already been sent to this email.']);
        }

        // If user exists, add them directly
        if ($existingUser) {
            // Add to primary project
            ProjectMember::create([
                'project_id' => $project->id,
                'user_id' => $existingUser->id,
                'role' => $role,
                'invited_at' => now(),
                'accepted_at' => now(),
            ]);

            // Add to additional projects (if not already a member)
            foreach ($additionalProjectIds as $additionalProjectId) {
                $additionalProject = Project::find($additionalProjectId);
                if ($additionalProject && ! $additionalProject->hasMember($existingUser)) {
                    ProjectMember::create([
                        'project_id' => $additionalProjectId,
                        'user_id' => $existingUser->id,
                        'role' => $role,
                        'invited_at' => now(),
                        'accepted_at' => now(),
                    ]);
                }
            }

            $projectCount = 1 + count($additionalProjectIds);

            return back()->with('success', "{$existingUser->name} has been added to {$projectCount} project(s).");
        }

        // Create a passwordless user
        $newUser = User::create([
            'name' => explode('@', $email)[0], // Use email prefix as temp name
            'email' => $email,
            'password' => null, // No password yet - will set on accept
        ]);

        // Add to primary project
        ProjectMember::create([
            'project_id' => $project->id,
            'user_id' => $newUser->id,
            'role' => $role,
            'invited_at' => now(),
            'accepted_at' => null, // Not accepted yet
        ]);

        // Add to additional projects
        foreach ($additionalProjectIds as $additionalProjectId) {
            ProjectMember::create([
                'project_id' => $additionalProjectId,
                'user_id' => $newUser->id,
                'role' => $role,
                'invited_at' => now(),
                'accepted_at' => null,
            ]);
        }

        // Create invitation for tracking and email
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'email' => $email,
            'role' => $role,
            'token' => ProjectInvitation::generateToken(),
            'expires_at' => now()->addDays(7),
            'invited_by' => $request->user()->id,
        ]);

        // Attach additional projects to the invitation for reference
        if (! empty($additionalProjectIds)) {
            $additionalProjectsData = [];
            foreach ($additionalProjectIds as $additionalProjectId) {
                $additionalProjectsData[$additionalProjectId] = ['role' => $role->value];
            }
            $invitation->additionalProjects()->attach($additionalProjectsData);
        }

        // Send invitation email
        Notification::route('mail', $email)
            ->notify(new TeamInvitationNotification($invitation, $project));

        return back()->with('success', "Invitation sent to {$email}.");
    }

    public function updateRole(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        Gate::authorize('manageTeam', $project);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Member role updated.');
    }

    public function remove(Project $project, ProjectMember $member): RedirectResponse
    {
        Gate::authorize('removeMember', $project);

        // Cannot remove the owner
        if ($member->user_id === $project->user_id) {
            return back()->withErrors(['error' => 'Cannot remove the project owner.']);
        }

        $member->delete();

        return back()->with('success', 'Team member removed.');
    }

    public function cancelInvitation(Project $project, ProjectInvitation $invitation): RedirectResponse
    {
        Gate::authorize('manageTeam', $project);

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled.');
    }
}
