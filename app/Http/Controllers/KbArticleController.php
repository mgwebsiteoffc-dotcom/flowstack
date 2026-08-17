<?php

namespace App\Http\Controllers;

use App\Http\Requests\KbArticleRequest;
use App\Models\KbArticle;
use App\Models\KbArticleTag;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->input('q');

        $query = KbArticle::published()->with('category');

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        $articles = $query->search($term)->latest()->paginate(15)->withQueryString();

        return view('kb.search', compact('articles', 'term'));
    }

    public function create()
    {
        $categories = KbCategory::orderBy('order_index')->get();
        $tags = KbArticleTag::orderBy('name')->get();

        return view('kb.articles.create', compact('categories', 'tags'));
    }

    public function store(KbArticleRequest $request)
    {
        $validated = $request->validated();

        $article = KbArticle::create($validated + [
            'tenant_id' => app('currentTenant')->id,
            'created_by' => auth()->id(),
            'published_at' => ($validated['status'] ?? 'draft') === 'published' ? now() : null,
        ]);

        $article->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('kb.articles.show', $article)->with('success', 'Article saved.');
    }

    public function show(KbArticle $article)
    {
        if ($article->status === 'published') {
            $article->increment('view_count');
        }

        $article->load('category', 'tags', 'creator', 'comments.user');

        $related = KbArticle::published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->latest()
            ->take(5)
            ->get();

        return view('kb.articles.show', compact('article', 'related'));
    }

    public function edit(KbArticle $article)
    {
        $categories = KbCategory::orderBy('order_index')->get();
        $tags = KbArticleTag::orderBy('name')->get();

        return view('kb.articles.edit', compact('article', 'categories', 'tags'));
    }

    public function update(KbArticleRequest $request, KbArticle $article)
    {
        $validated = $request->validated();

        $article->update($validated + [
            'published_at' => ($validated['status'] ?? 'draft') === 'published'
                ? ($article->published_at ?? now())
                : null,
        ]);

        $article->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('kb.articles.show', $article)->with('success', 'Article updated.');
    }

    public function destroy(KbArticle $article)
    {
        $article->delete();

        return redirect()->route('kb.index')->with('success', 'Article deleted.');
    }

    public function storeComment(Request $request, KbArticle $article)
    {
        $validated = $request->validate(['comment' => ['required', 'string', 'max:2000']]);

        \App\Models\KbArticleComment::create([
            'tenant_id' => $article->tenant_id,
            'article_id' => $article->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function feedback(Request $request, KbArticle $article)
    {
        $request->validate(['helpful' => ['required', 'boolean']]);

        // Lightweight feedback - store counts in the session for now.
        $key = $request->boolean('helpful') ? 'kb_feedback_yes' : 'kb_feedback_no';
        $count = (int) session($key, 0);
        session([$key => $count + 1]);

        return back()->with('success', 'Thanks for your feedback!');
    }
}
