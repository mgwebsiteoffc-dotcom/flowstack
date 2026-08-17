<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Policies\Concerns\ManagesTeamPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
 use HandlesAuthorization, ManagesTeamPermissions;

 public function viewAny(User $user): bool
 {
 return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
 }

 public function view(User $user, Report $report): bool
 {
 return $this->managesClient($user, $report->client);
 }

 public function create(User $user): bool
 {
 return in_array($user->role, ['admin', 'ops_manager', 'account_manager'], true);
 }

 public function update(User $user, Report $report): bool
 {
 return $this->managesClient($user, $report->client);
 }

 public function delete(User $user, Report $report): bool
 {
 return $this->isManager($user);
 }

 public function share(User $user, Report $report): bool
 {
 return $this->update($user, $report);
 }
}
