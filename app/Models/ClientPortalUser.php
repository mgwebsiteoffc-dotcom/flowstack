<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Portal credential for a client. Deliberately extends Authenticatable so it
 * can be used with the dedicated 'portal' session guard; it has nothing to do
 * with the internal team auth.
 */
class ClientPortalUser extends Authenticatable
{
    use Notifiable, TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'name', 'email', 'password',
        'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function requests()
    {
        return $this->hasMany(ClientRequest::class, 'submitted_by');
    }
}
