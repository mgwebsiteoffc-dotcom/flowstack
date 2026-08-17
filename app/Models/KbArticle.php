<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KbArticle extends Model
{
    use SoftDeletes, TenantScoped, Sluggable;

    protected $fillable = [
        'tenant_id', 'category_id', 'title', 'slug', 'content', 'status',
        'visibility', 'view_count', 'is_featured', 'created_by', 'published_at',
        'video_url',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ],
        ];
    }

    public function category()
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags()
    {
        return $this->belongsToMany(KbArticleTag::class, 'kb_article_tag_pivot');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        // MySQL FULLTEXT where available; LIKE fallback keeps SQLite/MariaDB happy.
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");
        });
    }

    /**
     * Sanitized HTML for rendering: strips <script>, event handlers and
     * javascript: URLs so {!! !!} is safe in the article view.
     */
    public function safeContent(): string
    {
        $html = $this->content ?? '';

        // Remove script/style blocks entirely.
        $html = preg_replace('/<\/?(script|style)\b[^>]*>/i', '', $html) ?? $html;

        // Remove on* event attributes and javascript: URLs.
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return $html;
    }

    /**
     * Extract h2/h3 headings for the auto-generated table of contents.
     */
    public function toc(): array
    {
        preg_match_all('/<h([23])[^>]*>(.*?)<\/h\1>/i', $this->content ?? '', $matches);

        $items = [];
        foreach ($matches[0] as $i => $tag) {
            $text = trim(strip_tags($matches[2][$i]));
            if ($text === '') {
                continue;
            }
            $items[] = [
                'level' => (int) $matches[1][$i],
                'text' => $text,
                'id' => 'heading-'.$i.'-'.strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text)),
            ];
        }

        return $items;
    }
}
