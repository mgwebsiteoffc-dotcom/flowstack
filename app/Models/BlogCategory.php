<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_category', 'category_id', 'post_id');
    }

    public function publishedPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_category', 'category_id', 'post_id')->published();
    }
}
