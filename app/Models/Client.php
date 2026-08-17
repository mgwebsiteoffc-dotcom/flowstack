<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id', 'company_name', 'industry', 'website', 'logo', 'address',
        'city', 'state', 'country', 'gstin', 'status', 'health_score',
        'health_score_reason', 'monthly_retainer', 'contract_start_date',
        'contract_end_date', 'account_manager_id', 'lead_id',
        'bikribook_customer_id', 'onboarding_completed_at',
        'portal_access_enabled', 'notes',
    ];

    protected $casts = [
        'monthly_retainer' => 'decimal:2',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'onboarding_completed_at' => 'datetime',
        'portal_access_enabled' => 'boolean',
    ];

    public const STATUSES = ['active', 'inactive', 'onboarding', 'offboarding'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function billingContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_billing_contact', true);
    }

    public function services()
    {
        return $this->hasMany(ClientService::class);
    }

    public function notes()
    {
        return $this->hasMany(ClientNote::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(ClientTeamMember::class);
    }

    public function onboardingItems()
    {
        return $this->hasMany(ClientOnboardingItem::class)->orderBy('order_index');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function folders()
    {
        return $this->hasMany(FileFolder::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function portalUsers()
    {
        return $this->hasMany(ClientPortalUser::class);
    }

    public function requests()
    {
        return $this->hasMany(ClientRequest::class);
    }

    public function approvals()
    {
        return $this->hasMany(ClientApproval::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function onboardingProgress(): int
    {
        $total = $this->onboardingItems()->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->onboardingItems()->where('is_completed', true)->count() * 100 / $total);
    }

    public function totalBilled(): float
    {
        return (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('total_amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->invoices()->where('status', 'paid')->sum('paid_amount');
    }

    public function outstandingBalance(): float
    {
        return max(0, $this->totalBilled() - $this->totalPaid());
    }

    public function hasService(string $serviceType): bool
    {
        return $this->services()->where('service_type', $serviceType)->exists();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/tenants/'.$this->tenant_id.'/clients/'.$this->id.'/logo/'.$this->logo);
        }

        return null;
    }

    public function contractExpiresSoon(int $days = 30): bool
    {
        return $this->contract_end_date && $this->contract_end_date->isFuture()
            && $this->contract_end_date->lte(now()->addDays($days));
    }
}
