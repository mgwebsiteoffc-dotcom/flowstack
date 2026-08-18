<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class IntegrationToken extends Model
{
    use TenantScoped;

    protected $fillable = ['tenant_id', 'provider', 'token_json', 'email', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
