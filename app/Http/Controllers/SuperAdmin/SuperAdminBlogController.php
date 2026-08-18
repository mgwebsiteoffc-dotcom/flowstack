<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Blog management for the public marketing website (super admin only).
 */
class SuperAdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with('categories');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->latest()->paginate(15)->withQueryString();
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();

        return view('super-admin.blog.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('super-admin.blog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blog', 'public');
        }

        $post = BlogPost::create($data);
        $post->categories()->sync($request->input('category_ids', []));

        return redirect()->route('super-admin.blog.index')->with('success', 'Blog post saved.');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('super-admin.blog.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $this->validated($request, $post);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blog', 'public');
        }

        $post->update($data);
        $post->categories()->sync($request->input('category_ids', []));

        return redirect()->route('super-admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();

        return back()->with('success', 'Blog post deleted.');
    }

    // --- Categories ---

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Category added.');
    }

    public function destroyCategory(BlogCategory $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    protected function validated(Request $request, ?BlogPost $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'.($post ? ','.$post->id : '')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:blog_categories,id'],
        ]);

        // Defaults must OVERRIDE nulls - use ?? / ?: (the + operator keeps nulls).
        $data['slug'] = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['title']);
        $data['published_at'] = ! empty($data['published_at']) ? $data['published_at'] : now();
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return $data;
    }
}
