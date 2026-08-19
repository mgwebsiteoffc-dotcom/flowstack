<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'user_id', 'endpoint', 'public_key', 'auth_token', 'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
