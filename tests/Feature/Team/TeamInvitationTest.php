<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->project = Project::factory()->create(['user_id' => $this->owner->id]);
});

describe('team page access', function () {
    test('guests cannot access team page', function () {
        $this->get(route('projects.team', $this->project))
            ->assertRedirect(route('login'));
    });

    test('owner can access team page', function () {
        $this->actingAs($this->owner)
            ->get(route('projects.team', $this->project))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('projects/team')
                ->has('project')
                ->has('members')
                ->has('owner')
                ->has('pendingInvitations')
                ->has('roles')
                ->has('canManageTeam')
            );
    });

    test('team member can access team page', function () {
        $member = User::factory()->create();
        ProjectMember::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $member->id,
            'role' => ProjectRole::Viewer,
        ]);

        $this->actingAs($member)
            ->get(route('projects.team', $this->project))
            ->assertOk();
    });

    test('non-member cannot access team page', function () {
        $nonMember = User::factory()->create();

        $this->actingAs($nonMember)
            ->get(route('projects.team', $this->project))
            ->assertForbidden();
    });
});

describe('invite team member', function () {
    test('owner can invite user by email', function () {
        Notification::fake();

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'newuser@example.com',
                'role' => 'viewer',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_invitations', [
            'project_id' => $this->project->id,
            'email' => 'newuser@example.com',
            'role' => 'viewer',
        ]);
    });

    test('owner can directly add existing user', function () {
        $existingUser = User::factory()->create();

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => $existingUser->email,
                'role' => 'editor',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $this->project->id,
            'user_id' => $existingUser->id,
            'role' => 'editor',
        ]);
    });

    test('admin can invite users', function () {
        Notification::fake();

        $admin = User::factory()->create();
        ProjectMember::factory()->admin()->create([
            'project_id' => $this->project->id,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'invitee@example.com',
                'role' => 'viewer',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_invitations', [
            'project_id' => $this->project->id,
            'email' => 'invitee@example.com',
        ]);
    });

    test('editor cannot invite users', function () {
        $editor = User::factory()->create();
        ProjectMember::factory()->editor()->create([
            'project_id' => $this->project->id,
            'user_id' => $editor->id,
        ]);

        $this->actingAs($editor)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'invitee@example.com',
                'role' => 'viewer',
            ])
            ->assertForbidden();
    });

    test('viewer cannot invite users', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $this->actingAs($viewer)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'invitee@example.com',
                'role' => 'viewer',
            ])
            ->assertForbidden();
    });

    test('cannot invite already member user', function () {
        $existingMember = User::factory()->create();
        ProjectMember::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $existingMember->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => $existingMember->email,
                'role' => 'viewer',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email']);
    });

    test('cannot send duplicate invitation', function () {
        Notification::fake();

        ProjectInvitation::factory()->create([
            'project_id' => $this->project->id,
            'email' => 'pending@example.com',
            'invited_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'pending@example.com',
                'role' => 'viewer',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['email']);
    });

    test('owner can invite user with access to additional projects', function () {
        Notification::fake();

        $additionalProject = Project::factory()->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'newuser@example.com',
                'role' => 'editor',
                'additional_project_ids' => [$additionalProject->id],
            ])
            ->assertRedirect();

        $invitation = ProjectInvitation::where('email', 'newuser@example.com')->first();

        expect($invitation)->not->toBeNull();
        expect($invitation->additionalProjects)->toHaveCount(1);
        expect($invitation->additionalProjects->first()->id)->toBe($additionalProject->id);
    });

    test('owner can directly add existing user to multiple projects', function () {
        $existingUser = User::factory()->create();
        $additionalProject = Project::factory()->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => $existingUser->email,
                'role' => 'editor',
                'additional_project_ids' => [$additionalProject->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_members', [
            'project_id' => $this->project->id,
            'user_id' => $existingUser->id,
            'role' => 'editor',
        ]);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $additionalProject->id,
            'user_id' => $existingUser->id,
            'role' => 'editor',
        ]);
    });

    test('cannot grant access to projects not owned by inviter', function () {
        $otherOwner = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherOwner->id]);

        $this->actingAs($this->owner)
            ->post(route('projects.team.invite', $this->project), [
                'email' => 'newuser@example.com',
                'role' => 'viewer',
                'additional_project_ids' => [$otherProject->id],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['additional_project_ids']);
    });

    test('team page includes available projects for owner', function () {
        $additionalProject = Project::factory()->create(['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('projects.team', $this->project))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('projects/team')
                ->has('availableProjects', 1)
                ->where('availableProjects.0.id', $additionalProject->id)
            );
    });
});

describe('update member role', function () {
    test('owner can update member role', function () {
        $member = ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($this->owner)
            ->patch(route('projects.team.update-role', [$this->project, $member]), [
                'role' => 'editor',
            ])
            ->assertRedirect();

        expect($member->fresh()->role)->toBe(ProjectRole::Editor);
    });

    test('admin can update member role', function () {
        $admin = User::factory()->create();
        ProjectMember::factory()->admin()->create([
            'project_id' => $this->project->id,
            'user_id' => $admin->id,
        ]);

        $member = ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('projects.team.update-role', [$this->project, $member]), [
                'role' => 'editor',
            ])
            ->assertRedirect();

        expect($member->fresh()->role)->toBe(ProjectRole::Editor);
    });

    test('viewer cannot update member role', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $member = ProjectMember::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($viewer)
            ->patch(route('projects.team.update-role', [$this->project, $member]), [
                'role' => 'editor',
            ])
            ->assertForbidden();
    });
});

describe('remove team member', function () {
    test('owner can remove member', function () {
        $member = ProjectMember::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('projects.team.remove', [$this->project, $member]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_members', [
            'id' => $member->id,
        ]);
    });

    test('admin can remove member', function () {
        $admin = User::factory()->create();
        ProjectMember::factory()->admin()->create([
            'project_id' => $this->project->id,
            'user_id' => $admin->id,
        ]);

        $member = ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('projects.team.remove', [$this->project, $member]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_members', [
            'id' => $member->id,
        ]);
    });

    test('viewer cannot remove members', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $member = ProjectMember::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->actingAs($viewer)
            ->delete(route('projects.team.remove', [$this->project, $member]))
            ->assertForbidden();
    });
});

describe('cancel invitation', function () {
    test('owner can cancel invitation', function () {
        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $this->project->id,
            'invited_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('projects.invitations.cancel', [$this->project, $invitation]))
            ->assertRedirect();

        $this->assertDatabaseMissing('project_invitations', [
            'id' => $invitation->id,
        ]);
    });

    test('viewer cannot cancel invitation', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $this->project->id,
            'invited_by' => $this->owner->id,
        ]);

        $this->actingAs($viewer)
            ->delete(route('projects.invitations.cancel', [$this->project, $invitation]))
            ->assertForbidden();
    });
});

describe('accept invitation', function () {
    test('valid invitation shows accept page for guests', function () {
        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $this->project->id,
            'invited_by' => $this->owner->id,
        ]);

        $this->get(route('invitations.accept', $invitation->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('invitations/accept')
                ->has('invitation')
            );
    });

    test('expired invitation shows expired page', function () {
        $invitation = ProjectInvitation::factory()->expired()->create([
            'project_id' => $this->project->id,
            'invited_by' => $this->owner->id,
        ]);

        $this->get(route('invitations.accept', $invitation->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('invitations/expired')
                ->has('project')
            );
    });

    test('invalid token shows invalid page', function () {
        $this->get(route('invitations.accept', 'invalid-token'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('invitations/invalid')
                ->has('message')
            );
    });

    test('already used invitation shows invalid page', function () {
        $invitation = ProjectInvitation::factory()->accepted()->create([
            'project_id' => $this->project->id,
            'invited_by' => $this->owner->id,
        ]);

        $this->get(route('invitations.accept', $invitation->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('invitations/invalid')
            );
    });

    test('logged in user with matching email is added as member', function () {
        $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
        $invitation = ProjectInvitation::factory()->editor()->create([
            'project_id' => $this->project->id,
            'email' => 'invited@example.com',
            'invited_by' => $this->owner->id,
        ]);

        // In the new flow, memberships are created when inviting (with pending status)
        // Simulate this by creating the pending membership
        ProjectMember::factory()->pending()->create([
            'project_id' => $this->project->id,
            'user_id' => $invitedUser->id,
            'role' => 'editor',
        ]);

        $this->actingAs($invitedUser)
            ->get(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('dashboard'));

        // Verify membership is now accepted
        $this->assertDatabaseHas('project_members', [
            'project_id' => $this->project->id,
            'user_id' => $invitedUser->id,
            'role' => 'editor',
        ]);

        $member = ProjectMember::where('project_id', $this->project->id)
            ->where('user_id', $invitedUser->id)
            ->first();
        expect($member->accepted_at)->not->toBeNull();
        expect($invitation->fresh()->accepted_at)->not->toBeNull();
    });

    test('logged in user with different email is rejected', function () {
        $differentUser = User::factory()->create(['email' => 'different@example.com']);
        $invitation = ProjectInvitation::factory()->create([
            'project_id' => $this->project->id,
            'email' => 'invited@example.com',
            'invited_by' => $this->owner->id,
        ]);

        $this->actingAs($differentUser)
            ->get(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors(['invitation']);
    });

    test('accepting invitation grants access to all additional projects', function () {
        $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
        $additionalProject = Project::factory()->create(['user_id' => $this->owner->id]);

        $invitation = ProjectInvitation::factory()->editor()->create([
            'project_id' => $this->project->id,
            'email' => 'invited@example.com',
            'invited_by' => $this->owner->id,
        ]);
        $invitation->additionalProjects()->attach($additionalProject->id, ['role' => 'editor']);

        // In the new flow, memberships are created when inviting (with pending status)
        // Simulate this by creating the pending memberships for all projects
        ProjectMember::factory()->pending()->create([
            'project_id' => $this->project->id,
            'user_id' => $invitedUser->id,
            'role' => 'editor',
        ]);
        ProjectMember::factory()->pending()->create([
            'project_id' => $additionalProject->id,
            'user_id' => $invitedUser->id,
            'role' => 'editor',
        ]);

        $this->actingAs($invitedUser)
            ->get(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('dashboard'));

        // Verify both memberships are now accepted
        $primaryMember = ProjectMember::where('project_id', $this->project->id)
            ->where('user_id', $invitedUser->id)
            ->first();
        expect($primaryMember->accepted_at)->not->toBeNull();

        $additionalMember = ProjectMember::where('project_id', $additionalProject->id)
            ->where('user_id', $invitedUser->id)
            ->first();
        expect($additionalMember->accepted_at)->not->toBeNull();
    });
});

describe('project access', function () {
    test('owner can access project', function () {
        expect($this->project->hasMember($this->owner))->toBeTrue();
    });

    test('accepted member can access project', function () {
        $member = User::factory()->create();
        ProjectMember::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $member->id,
            'accepted_at' => now(),
        ]);

        expect($this->project->hasMember($member))->toBeTrue();
    });

    test('pending member cannot access project', function () {
        $member = User::factory()->create();
        ProjectMember::factory()->pending()->create([
            'project_id' => $this->project->id,
            'user_id' => $member->id,
        ]);

        expect($this->project->hasMember($member))->toBeFalse();
    });

    test('non-member cannot access project', function () {
        $nonMember = User::factory()->create();

        expect($this->project->hasMember($nonMember))->toBeFalse();
    });
});

describe('accessible projects', function () {
    test('user can see owned projects', function () {
        $projects = $this->owner->accessibleProjects()->get();

        expect($projects)->toHaveCount(1);
        expect($projects->first()->id)->toBe($this->project->id);
    });

    test('user can see projects where they are a member', function () {
        $member = User::factory()->create();
        ProjectMember::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $member->id,
        ]);

        $projects = $member->accessibleProjects()->get();

        expect($projects)->toHaveCount(1);
        expect($projects->first()->id)->toBe($this->project->id);
    });

    test('user can see both owned and member projects', function () {
        $ownedProject = Project::factory()->create(['user_id' => $this->owner->id]);

        $otherProject = Project::factory()->create();
        ProjectMember::factory()->create([
            'project_id' => $otherProject->id,
            'user_id' => $this->owner->id,
        ]);

        $projects = $this->owner->accessibleProjects()->get();

        expect($projects)->toHaveCount(3);
    });

    test('user cannot see projects they are not a member of', function () {
        $otherUser = User::factory()->create();
        Project::factory()->create(['user_id' => $otherUser->id]);

        $projects = $this->owner->accessibleProjects()->get();

        expect($projects)->toHaveCount(1);
    });
});

describe('role permissions', function () {
    test('owner can manage team', function () {
        $role = $this->project->getMemberRole($this->owner);

        expect($role)->toBe(ProjectRole::Owner);
        expect($role->canManageTeam())->toBeTrue();
    });

    test('admin can manage team', function () {
        $admin = User::factory()->create();
        ProjectMember::factory()->admin()->create([
            'project_id' => $this->project->id,
            'user_id' => $admin->id,
        ]);

        $role = $this->project->getMemberRole($admin);

        expect($role)->toBe(ProjectRole::Admin);
        expect($role->canManageTeam())->toBeTrue();
    });

    test('editor cannot manage team', function () {
        $editor = User::factory()->create();
        ProjectMember::factory()->editor()->create([
            'project_id' => $this->project->id,
            'user_id' => $editor->id,
        ]);

        $role = $this->project->getMemberRole($editor);

        expect($role)->toBe(ProjectRole::Editor);
        expect($role->canManageTeam())->toBeFalse();
    });

    test('viewer cannot manage team', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $role = $this->project->getMemberRole($viewer);

        expect($role)->toBe(ProjectRole::Viewer);
        expect($role->canManageTeam())->toBeFalse();
    });

    test('editor can edit records', function () {
        $editor = User::factory()->create();
        ProjectMember::factory()->editor()->create([
            'project_id' => $this->project->id,
            'user_id' => $editor->id,
        ]);

        $role = $this->project->getMemberRole($editor);

        expect($role->canEditRecords())->toBeTrue();
    });

    test('viewer cannot edit records', function () {
        $viewer = User::factory()->create();
        ProjectMember::factory()->viewer()->create([
            'project_id' => $this->project->id,
            'user_id' => $viewer->id,
        ]);

        $role = $this->project->getMemberRole($viewer);

        expect($role->canEditRecords())->toBeFalse();
    });
});
