<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
 use HasFactory, Notifiable, TenantScoped;

 protected $fillable = [
 'tenant_id', 'name', 'email', 'password', 'role', 'avatar', 'phone',
 'designation', 'timezone', 'is_active', 'hourly_cost', 'last_login_at',
 'email_verified_at',
 ];

 protected $hidden = ['password', 'remember_token'];

 protected function casts(): array
 {
 return [
 'password' => 'hashed',
 'email_verified_at' => 'datetime',
 'last_login_at' => 'datetime',
 'is_active' => 'boolean',
 'hourly_cost' => 'decimal:2',
 ];
 }

 public const ROLES = ['admin', 'ops_manager', 'account_manager', 'specialist'];

 public function tenant()
 {
 return $this->belongsTo(Tenant::class);
 }

 public function clientsManaged()
 {
 return $this->hasMany(Client::class, 'account_manager_id');
 }

 public function clientTeamMembers()
 {
 return $this->hasMany(ClientTeamMember::class);
 }

 public function projectMembers()
 {
 return $this->hasMany(ProjectMember::class);
 }

 public function projects()
 {
 return $this->belongsToMany(Project::class, 'project_members')->withPivot('role');
 }

 public function assignedTasks()
 {
 return $this->hasMany(Task::class, 'assigned_to');
 }

 public function createdTasks()
 {
 return $this->hasMany(Task::class, 'created_by');
 }

 public function timeEntries()
 {
 return $this->hasMany(TimeEntry::class);
 }

 public function taskComments()
 {
 return $this->hasMany(TaskComment::class);
 }

 public function isAdmin(): bool
 {
 return $this->role === 'admin';
 }

 public function isOpsManager(): bool
 {
 return $this->role === 'ops_manager';
 }

 public function isAccountManager(): bool
 {
 return $this->role === 'account_manager';
 }

 public function isSpecialist(): bool
 {
 return $this->role === 'specialist';
 }

    public function canAccessFinance(): bool
    {
        return in_array($this->role, ['admin', 'ops_manager', 'account_manager'], true);
    }

    /**
     * Whether this user may see payment/billing terms (amounts, rates, costs,
     * billable flags, retainer values, etc.) across shared screens.
     *
     * Admins always see financials. Everyone else is masked by default; the
     * tenant admin can grant visibility to other roles in Settings
     * (stored as a JSON array under the "financial_roles" setting). There is
     * deliberately no "unmask" toggle — the default is always masked.
     */
    public function canViewFinancials(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $raw = Setting::get('financial_roles');

        if ($raw === null || $raw === '') {
            // Default: only the ops/finance manager (besides admin) sees financials.
            return $this->isOpsManager();
        }

        $allowed = json_decode((string) $raw, true);

        if (! is_array($allowed)) {
            return $this->isOpsManager();
        }

        return in_array($this->role, $allowed, true);
    }

 public function getInitialsAttribute(): string
 {
 $parts = preg_split('/\s+/', trim($this->name)) ?: [];

 return mb_strtoupper(mb_substr(($parts[0] ?? '?'), 0, 1).mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
 }

 public function getAvatarUrlAttribute(): ?string
 {
 if ($this->avatar) {
 return asset('storage/tenants/'.$this->tenant_id.'/avatars/'.$this->avatar);
 }

 return null;
 }
}
