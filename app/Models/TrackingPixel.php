<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingPixel extends Model
{
    protected $fillable = ['name', 'provider', 'placement', 'code', 'is_active', 'priority'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public const PROVIDERS = ['facebook', 'google', 'tiktok', 'gtag', 'hotjar', 'custom'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority');
    }
}
