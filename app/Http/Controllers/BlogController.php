<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;

/**
 * Public blog (marketing website) - no auth, no tenant scoping.
 */
class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->with('categories')
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();
        $featured = BlogPost::published()->where('is_featured', true)->latest('published_at')->first();

        return view('blog.index', compact('posts', 'categories', 'featured'));
    }

    public function category(string $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()->published()->with('categories')->latest('published_at')->paginate(9);
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->orderBy('name')->get();

        return view('blog.index', compact('posts', 'categories', 'category'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->with('categories')->where('slug', $slug)->firstOrFail();

        $post->increment('view_count');

        $related = BlogPost::published()
            ->with('categories')
            ->where('id', '!=', $post->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('id', $post->categories->pluck('id')))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $latest = BlogPost::published()->where('id', '!=', $post->id)->latest('published_at')->limit(4)->get();

        return view('blog.show', compact('post', 'related', 'latest'));
    }
}
