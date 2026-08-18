<?php

namespace App\Http\Controllers;

use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;

class KbCategoryController extends Controller
{
 public function index(Request $request)
 {
 $search = $request->input('search');

 $categories = KbCategory::withCount([
 'publishedArticles' => fn ($q) => $q->where('visibility', 'all'),
 ])->orderBy('order_index')->get();

 $featured = KbArticle::published()->where('is_featured', true)->with('category')->latest()->take(5)->get();

 $recent = KbArticle::published()->with('category')->latest()->take(6)->get();

 return view('kb.index', compact('categories', 'featured', 'recent', 'search'));
 }
}
