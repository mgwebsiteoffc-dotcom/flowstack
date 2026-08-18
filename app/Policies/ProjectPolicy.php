<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization, ManagesTeamPermissions;

    public function viewAny(User $user): bool
    {
        // Everyone on the team can list projects they are entitled to see;
        // the controller filters by role.
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        if ($user->isAccountManager()) {
            return (int) $project->client?->account_manager_id === (int) $user->id;
        }

        // Specialist: only projects they are a member of.
        return $project->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->isManager($user)
            || ($user->isAccountManager() && (int) $project->client?->account_manager_id === (int) $user->id);
    }

    public function restore(User $user): bool
    {
        return $this->isManager($user);
    }

    public function forceDelete(User $user): bool
    {
        return $this->isAdmin($user);
    }
}
