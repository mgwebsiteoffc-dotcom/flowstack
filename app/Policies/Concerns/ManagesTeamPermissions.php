<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Shared role helpers for policies.
 */
trait ManagesTeamPermissions
{
    /**
     * admin + ops_manager have unrestricted access.
     */
    protected function isManager(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager'], true);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Does this user manage the given client (AM assigned or manager role)?
     */
    protected function managesClient(User $user, $client): bool
    {
        return $this->isManager($user)
            || ($client !== null && (int) $client->account_manager_id === (int) $user->id);
    }
}
