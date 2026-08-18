<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
 use HandlesAuthorization, ManagesTeamPermissions;

 public function viewAny(User $user): bool
 {
 return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
 }

 public function view(User $user, User $target): bool
 {
 return $this->isManager($user)
 || (int) $target->id === (int) $user->id
 || $user->isAccountManager();
 }

 public function create(User $user): bool
 {
 return $this->isManager($user);
 }

 public function update(User $user, User $target): bool
 {
 return $this->isManager($user) || (int) $target->id === (int) $user->id;
 }

 public function delete(User $user, User $target): bool
 {
 return $this->isAdmin($user) && (int) $target->id !== (int) $user->id;
 }

 public function setRole(User $user): bool
 {
 return $this->isAdmin($user);
 }
}
