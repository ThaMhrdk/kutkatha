<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\ForumComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForumController extends ApiController
{
    /**
     * Get forum topics.
     */
    public function topics(Request $request): JsonResponse
    {
        $query = ForumTopic::with(['user', 'posts'])
            ->withCount('posts');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $query->orderByDesc('is_pinned')
              ->orderByDesc('created_at');

        $topics = $query->paginate($request->get('per_page', 15));

        return $this->paginated($topics, 'Daftar topik berhasil diambil.');
    }

    /**
     * Create a new topic.
     */
    public function storeTopic(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:50',
                'description' => 'required|string|min:20|max:2000',
                'is_anonymous' => 'boolean',
            ]);

            $topic = ForumTopic::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . Str::random(5),
                'category' => $request->category,
                'description' => $request->description,
                'is_anonymous' => $request->boolean('is_anonymous'),
            ]);

            return $this->success($topic->load('user'), 'Topik berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get topic detail.
     */
    public function showTopic(ForumTopic $topic): JsonResponse
    {
        $topic->increment('views_count');
        $topic->load(['user', 'posts.user', 'posts.comments.user']);

        return $this->success($topic, 'Detail topik berhasil diambil.');
    }

    /**
     * Get posts for a topic.
     */
    public function posts(Request $request, ForumTopic $topic): JsonResponse
    {
        $query = $topic->posts()
            ->with(['user', 'comments.user'])
            ->withCount('comments');

        // Filter best answers first
        $query->orderByDesc('is_best_answer')
              ->orderByDesc('created_at');

        $posts = $query->paginate($request->get('per_page', 10));

        return $this->paginated($posts, 'Daftar postingan berhasil diambil.');
    }

    /**
     * Create a new post in a topic.
     */
    public function storePost(Request $request, ForumTopic $topic): JsonResponse
    {
        try {
            if ($topic->is_closed) {
                return $this->error('Topik sudah ditutup.', 400);
            }

            $request->validate([
                'content' => 'required|string|min:10|max:5000',
                'is_anonymous' => 'boolean',
            ]);

            $post = ForumPost::create([
                'topic_id' => $topic->id,
                'user_id' => Auth::id(),
                'content' => $request->content,
                'is_anonymous' => $request->boolean('is_anonymous'),
            ]);

            return $this->success($post->load('user'), 'Postingan berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get comments for a post.
     */
    public function comments(Request $request, ForumPost $post): JsonResponse
    {
        $comments = $post->comments()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 20));

        return $this->paginated($comments, 'Daftar komentar berhasil diambil.');
    }

    /**
     * Create a comment on a post.
     */
    public function storeComment(Request $request, ForumPost $post): JsonResponse
    {
        try {
            if ($post->topic->is_closed) {
                return $this->error('Topik sudah ditutup.', 400);
            }

            $request->validate([
                'content' => 'required|string|min:5|max:1000',
                'is_anonymous' => 'boolean',
            ]);

            $comment = ForumComment::create([
                'post_id' => $post->id,
                'user_id' => Auth::id(),
                'content' => $request->content,
                'is_anonymous' => $request->boolean('is_anonymous'),
            ]);

            return $this->success($comment->load('user'), 'Komentar berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get all topics for admin.
     */
    public function adminTopics(Request $request): JsonResponse
    {
        $query = ForumTopic::with('user')
            ->withCount('posts');

        // Filter by status
        if ($request->filled('is_closed')) {
            $query->where('is_closed', $request->boolean('is_closed'));
        }

        $topics = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($topics, 'Daftar topik admin berhasil diambil.');
    }

    /**
     * Delete a post (admin only).
     */
    public function deletePost(ForumPost $post): JsonResponse
    {
        // Delete all comments first
        $post->comments()->delete();
        $post->delete();

        return $this->success(null, 'Postingan berhasil dihapus.');
    }

    /**
     * Delete a comment (admin only).
     */
    public function deleteComment(ForumComment $comment): JsonResponse
    {
        $comment->delete();

        return $this->success(null, 'Komentar berhasil dihapus.');
    }
}
