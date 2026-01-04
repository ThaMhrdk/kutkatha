<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleController extends ApiController
{
    /**
     * Get published articles (public).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::with('author')
            ->where('status', 'published');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'views') {
            $query->orderBy('views_count', $sortOrder);
        } else {
            $query->orderBy('published_at', $sortOrder);
        }

        $articles = $query->paginate($request->get('per_page', 10));

        return $this->paginated($articles, 'Daftar artikel berhasil diambil.');
    }

    /**
     * Get article detail (public).
     */
    public function show(Article $article): JsonResponse
    {
        if ($article->status !== 'published') {
            // Only author can view unpublished articles
            if (!Auth::check() || (int) $article->author_id !== (int) Auth::id()) {
                return $this->error('Artikel tidak ditemukan.', 404);
            }
        }

        // Increment view count (async-friendly)
        $article->incrementViews();

        $article->load('author');

        return $this->success($article, 'Detail artikel berhasil diambil.');
    }

    /**
     * Create a new article (psikolog only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if (!$psikolog || !$psikolog->isVerified()) {
                return $this->error('Hanya psikolog terverifikasi yang dapat membuat artikel.', 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string|min:100',
                'excerpt' => 'nullable|string|max:500',
                'category' => 'required|string|max:50',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status' => 'in:draft,published',
            ]);

            $imagePath = null;
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('articles', 'public');
            }

            $article = Article::create([
                'author_id' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . Str::random(5),
                'content' => $request->content,
                'excerpt' => $request->excerpt ?? Str::limit(strip_tags($request->content), 200),
                'category' => $request->category,
                'featured_image' => $imagePath,
                'status' => $request->status ?? 'draft',
                'published_at' => $request->status === 'published' ? now() : null,
            ]);

            return $this->success($article, 'Artikel berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Update an article (psikolog only - own articles).
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        try {
            // Authorization handled by policy
            $this->authorize('update', $article);

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string|min:100',
                'excerpt' => 'nullable|string|max:500',
                'category' => 'sometimes|string|max:50',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status' => 'sometimes|in:draft,published',
            ]);

            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('articles', 'public');
                $article->featured_image = $imagePath;
            }

            $article->fill($request->only([
                'title', 'content', 'excerpt', 'category', 'status'
            ]));

            // Update published_at if publishing for the first time
            if ($request->status === 'published' && !$article->published_at) {
                $article->published_at = now();
            }

            // Update slug if title changed
            if ($request->filled('title') && $article->isDirty('title')) {
                $article->slug = Str::slug($request->title) . '-' . Str::random(5);
            }

            $article->save();

            return $this->success($article->fresh(), 'Artikel berhasil diperbarui.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Delete an article (psikolog only - own articles).
     */
    public function destroy(Article $article): JsonResponse
    {
        // Authorization handled by policy
        $this->authorize('delete', $article);

        $article->delete();

        return $this->success(null, 'Artikel berhasil dihapus.');
    }
}
