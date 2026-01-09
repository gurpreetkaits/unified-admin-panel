<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token): Response|RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->with('project')
            ->first();

        if (! $invitation) {
            return Inertia::render('invitations/invalid', [
                'message' => 'This invitation is invalid or has already been used.',
            ]);
        }

        if ($invitation->isExpired()) {
            return Inertia::render('invitations/expired', [
                'project' => $invitation->project->name,
            ]);
        }

        // If user is not logged in, show accept page
        if (! $request->user()) {
            // Check if user with this email exists and if they have a password
            $user = User::where('email', $invitation->email)->first();
            $userExists = $user !== null;
            $hasPassword = $user && $user->password !== null;

            return Inertia::render('invitations/accept', [
                'invitation' => [
                    'token' => $invitation->token,
                    'email' => $invitation->email,
                    'role' => $invitation->role->label(),
                    'project' => $invitation->project->name,
                    'userExists' => $userExists,
                    'hasPassword' => $hasPassword,
                ],
            ]);
        }

        // User is logged in - process acceptance
        return $this->processAcceptance($request, $invitation);
    }

    public function confirm(Request $request, string $token): RedirectResponse
    {
        $invitation = ProjectInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return back()->withErrors(['email' => 'Invalid or expired invitation.']);
        }

        // If user is already logged in, process acceptance
        if ($request->user()) {
            return $this->processAcceptance($request, $invitation);
        }

        // Get the user
        $user = User::where('email', $invitation->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Check if user has a password (existing user) or needs to set one (new invite)
        if ($user->password === null) {
            // New user - validate and set password
            $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'name' => ['required', 'string', 'max:255'],
            ]);

            $user->update([
                'name' => $request->name,
                'password' => \Hash::make($request->password),
            ]);
        } else {
            // Existing user - validate password
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (! \Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'The provided password is incorrect.']);
            }
        }

        // Log the user in
        \Auth::login($user);
        $request->session()->regenerate();

        // Now process the acceptance with the logged in user
        return $this->processAcceptance($request, $invitation);
    }

    private function processAcceptance(Request $request, ProjectInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        // Check if user email matches invitation
        if ($user->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->withErrors(['invitation' => 'This invitation was sent to a different email address.']);
        }

        // Mark all pending memberships for this user as accepted
        $acceptedMemberships = ProjectMember::where('user_id', $user->id)
            ->whereNull('accepted_at')
            ->get();

        $addedProjects = [];
        foreach ($acceptedMemberships as $membership) {
            $membership->update(['accepted_at' => now()]);
            $addedProjects[] = $membership->project->name;
        }

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Set the primary project as current project
        $request->session()->put('current_project_id', $invitation->project_id);

        if (empty($addedProjects)) {
            return redirect()->route('dashboard')
                ->with('info', 'You are already a member of all the invited projects.');
        }

        $projectNames = implode(', ', $addedProjects);

        return redirect()->route('dashboard')
            ->with('success', "Welcome! You've been added to: {$projectNames}");
    }
}
