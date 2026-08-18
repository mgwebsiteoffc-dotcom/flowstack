<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'type', 'name', 'color', 'meta', 'is_active', 'order_index',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];
}
