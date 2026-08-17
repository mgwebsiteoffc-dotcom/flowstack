<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization, ManagesTeamPermissions;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->managesClient($user, $client);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager'], true);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->managesClient($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        // ops_manager may soft-delete clients but never permanently.
        return in_array($user->role, ['admin', 'ops_manager'], true);
    }

    public function restore(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Client-level financial data (invoices, expenses, profitability).
     */
    public function viewFinance(User $user, Client $client): bool
    {
        return $this->managesClient($user, $client);
    }
}
