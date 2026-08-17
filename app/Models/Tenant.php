<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'custom_domain', 'logo', 'email', 'phone', 'address',
        'plan_id', 'plan_started_at', 'plan_expires_at', 'trial_ends_at',
        'is_trial', 'is_active', 'max_users', 'max_clients', 'settings',
        'lead365_webhook_secret', 'bikribook_api_key', 'bikribook_api_secret',
        'bikribook_company_id', 'bikribook_base_url',
    ];

    protected $casts = [
        'plan_started_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'is_trial' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = ['bikribook_api_key', 'bikribook_api_secret', 'lead365_webhook_secret'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPayments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * BikriBook API key helpers - always encrypted at rest, decrypted only
     * in BikriBookService. Never log the decrypted value.
     */
    public function getBikriBookApiKeyDecrypted(): ?string
    {
        if (! $this->bikribook_api_key) {
            return null;
        }

        return Crypt::decryptString($this->bikribook_api_key);
    }

    public function setBikriBookApiKeyEncrypted(?string $plainKey): void
    {
        $this->bikribook_api_key = $plainKey ? Crypt::encryptString($plainKey) : null;
    }

    public function getBikriBookApiSecretDecrypted(): ?string
    {
        if (! $this->bikribook_api_secret) {
            return null;
        }

        return Crypt::decryptString($this->bikribook_api_secret);
    }

    public function setBikriBookApiSecretEncrypted(?string $plainSecret): void
    {
        $this->bikribook_api_secret = $plainSecret ? Crypt::encryptString($plainSecret) : null;
    }

    public function getBikriBookBaseUrl(): string
    {
        return $this->bikribook_base_url ?: config('services.bikribook.base_url');
    }

    /**
     * Last 4 characters of the decrypted API key for masked UI display.
     */
    public function bikriBookKeyLastFour(): ?string
    {
        if (! $this->bikribook_api_key) {
            return null;
        }

        try {
            return substr($this->getBikriBookApiKeyDecrypted(), -4);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Convenience accessor for tenant-level settings JSON.
     */
    public function setting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];

        return data_get($settings, $key, $default);
    }

    /**
     * Days remaining in the trial (0 when not on trial / expired).
     */
    public function trialDaysRemaining(): int
    {
        if (! $this->is_trial || $this->trial_ends_at === null) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    public function isSubscriptionActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->is_trial && $this->trial_ends_at) {
            return $this->trial_ends_at->isFuture();
        }

        if ($this->plan_expires_at) {
            return $this->plan_expires_at->isFuture();
        }

        return true;
    }
}
