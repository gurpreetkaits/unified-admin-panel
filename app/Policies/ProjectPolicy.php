<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canManageSettings() ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->user_id === $user->id;
    }

    /**
     * Determine whether the user can view tables.
     */
    public function viewTables(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canViewTables() ?? false;
    }

    /**
     * Determine whether the user can edit records.
     */
    public function editRecords(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canEditRecords() ?? false;
    }

    /**
     * Determine whether the user can delete records.
     */
    public function deleteRecords(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canDeleteRecords() ?? false;
    }

    /**
     * Determine whether the user can manage project settings.
     */
    public function manageSettings(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canManageSettings() ?? false;
    }

    /**
     * Determine whether the user can manage team members.
     */
    public function manageTeam(User $user, Project $project): bool
    {
        $role = $project->getMemberRole($user);

        return $role?->canManageTeam() ?? false;
    }

    /**
     * Determine whether the user can invite team members.
     */
    public function invite(User $user, Project $project): bool
    {
        return $this->manageTeam($user, $project);
    }

    /**
     * Determine whether the user can remove team members.
     */
    public function removeMember(User $user, Project $project): bool
    {
        return $this->manageTeam($user, $project);
    }
}
