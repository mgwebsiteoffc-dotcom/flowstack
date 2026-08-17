<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization, ManagesTeamPermissions;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true)
            || true; // specialists can see leads assigned to them (filtered in controller)
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($this->isManager($user)) {
            return true;
        }

        if ($user->isAccountManager()) {
            return true;
        }

        return (int) $lead->assigned_to === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->isManager($user);
    }

    public function convert(User $user, Lead $lead): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }
}
