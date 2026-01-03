<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('author')->published();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $featuredArticle = Article::with('author')->published()->latest('published_at')->first();

        $articles = $query->latest('published_at')->paginate(12);

        $categories = Article::published()
            ->distinct()
            ->pluck('category')
            ->filter();

        $popularArticles = Article::published()
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        return view('articles.index', compact('articles', 'categories', 'featuredArticle', 'popularArticles'));
    }

    public function show(Article $article)
    {
        if ($article->status !== 'published') {
            abort(404);
        }

        $article->incrementViews();
        $article->load('author');

        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->take(3)
            ->get();

        $popularArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->orderBy('views_count', 'desc')
            ->take(5)
            ->get();

        return view('articles.show', compact('article', 'relatedArticles', 'popularArticles'));
    }
}
