<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization, ManagesTeamPermissions;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->managesClient($user, $invoice->client);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->managesClient($user, $invoice->client);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->isManager($user)
            || ($user->isAccountManager() && $this->managesClient($user, $invoice->client));
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }

    public function syncToBikriBook(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }
}
