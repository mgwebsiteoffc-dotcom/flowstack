<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class KbCategory extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'icon', 'color',
        'order_index', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function articles()
    {
        return $this->hasMany(KbArticle::class);
    }

    public function publishedArticles()
    {
        return $this->hasMany(KbArticle::class)->where('status', 'published');
    }
}
