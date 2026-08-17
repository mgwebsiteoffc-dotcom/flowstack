<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization, ManagesTeamPermissions;

    public function viewAny(User $user): bool
    {
        // Specialists only ever see their own tasks (controller filters by
        // assigned_to/created_by); everyone else sees team tasks.
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        if ($user->isAccountManager()) {
            return $task->client === null || $this->managesClient($user, $task->client);
        }

        // Specialist: assigned to them, created by them, or part of a project they belong to.
        return (int) $task->assigned_to === (int) $user->id
            || (int) $task->created_by === (int) $user->id
            || ($task->project !== null && $task->project->members()->where('user_id', $user->id)->exists());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        if ($user->isAccountManager()) {
            return $task->client === null || $this->managesClient($user, $task->client);
        }

        return (int) $task->created_by === (int) $user->id;
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
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
