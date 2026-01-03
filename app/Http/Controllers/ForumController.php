<?php

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\ForumComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get unique categories from topics
        $categories = ForumTopic::distinct()->pluck('category')->filter()->values();

        // Get pinned topics
        $pinnedTopics = ForumTopic::with('user')
            ->withCount('posts')
            ->where('is_pinned', true)
            ->latest()
            ->get();

        // Get regular topics (excluding pinned)
        $query = ForumTopic::with('user')
            ->withCount('posts')
            ->where('is_pinned', false);

        // Filter by search
        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        // Filter by category
        if (request('category')) {
            $query->where('category', request('category'));
        }

        $topics = $query->latest()->paginate(15);

        // Stats for sidebar
        $totalTopics = ForumTopic::count();
        $totalPosts = ForumPost::count();
        $activeMembers = ForumTopic::distinct('user_id')->count('user_id');

        // Top contributors - get users with most forum posts
        $topContributors = \App\Models\User::select('users.*')
            ->selectRaw('(SELECT COUNT(*) FROM forum_posts WHERE forum_posts.user_id = users.id) as posts_count')
            ->whereRaw('(SELECT COUNT(*) FROM forum_posts WHERE forum_posts.user_id = users.id) > 0')
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get();

        return view('forum.index', compact('topics', 'categories', 'pinnedTopics', 'totalTopics', 'totalPosts', 'activeMembers', 'topContributors'));
    }

    public function create()
    {
        return view('forum.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['user_id'] = auth()->id();

        ForumTopic::create($validated);

        return redirect()->route('forum.index')
            ->with('success', 'Topik forum berhasil dibuat');
    }

    public function topic(ForumTopic $topic)
    {
        $topic->load('user');

        // Get paginated posts for this topic
        $posts = ForumPost::with(['user', 'comments.user'])
            ->where('topic_id', $topic->id)
            ->latest()
            ->paginate(15);

        // Get related topics (same category, excluding current)
        $relatedTopics = ForumTopic::withCount('posts')
            ->where('category', $topic->category)
            ->where('id', '!=', $topic->id)
            ->latest()
            ->limit(5)
            ->get();

        // Increment views count
        $topic->increment('views_count');

        return view('forum.topic', compact('topic', 'posts', 'relatedTopics'));
    }

    public function showPost(ForumPost $post)
    {
        $post->load(['author', 'topic', 'comments.author']);

        return view('forum.post', compact('post'));
    }

    public function createPost(ForumTopic $topic)
    {
        return view('forum.create-post', compact('topic'));
    }

    public function storePost(Request $request, ForumTopic $topic)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $validated['topic_id'] = $topic->id;
        $validated['user_id'] = auth()->id();

        ForumPost::create($validated);

        return redirect()->route('forum.topic', $topic)
            ->with('success', 'Balasan berhasil dibuat');
    }

    public function storeComment(Request $request, ForumPost $post)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $validated['post_id'] = $post->id;
        $validated['user_id'] = auth()->id();

        ForumComment::create($validated);

        return back()->with('success', 'Komentar berhasil ditambahkan');
    }
}
