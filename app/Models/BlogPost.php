<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'cover_image', 'status',
        'author_name', 'published_at', 'meta_title', 'meta_description',
        'is_featured', 'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    public const STATUSES = ['draft', 'published'];

    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_post_category', 'post_id', 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: ($this->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($this->content ?? ''), 160));
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/blog/'.$this->cover_image) : null;
    }
}
