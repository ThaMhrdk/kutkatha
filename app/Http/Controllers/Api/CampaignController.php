<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignController extends ApiController
{
    /**
     * Get active campaigns (public).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::with('creator')
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by target audience
        if ($request->filled('target_audience')) {
            $query->where(function($q) use ($request) {
                $q->where('target_audience', $request->target_audience)
                  ->orWhere('target_audience', 'all');
            });
        }

        // Featured first
        $query->orderByDesc('is_featured')
              ->orderByDesc('created_at');

        $campaigns = $query->paginate($request->get('per_page', 10));

        return $this->paginated($campaigns, 'Daftar kampanye berhasil diambil.');
    }

    /**
     * Get campaign detail.
     */
    public function show(Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== 'active') {
            return $this->error('Kampanye tidak ditemukan.', 404);
        }

        $campaign->incrementViews();
        $campaign->load('creator');

        return $this->success($campaign, 'Detail kampanye berhasil diambil.');
    }

    /**
     * Get all campaigns for government (admin).
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Campaign::with('creator');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($campaigns, 'Daftar kampanye berhasil diambil.');
    }

    /**
     * Create a new campaign (government only).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'content' => 'required|string|min:100',
                'category' => 'required|string',
                'target_audience' => 'required|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'is_featured' => 'boolean',
                'status' => 'in:draft,active',
            ]);

            $imagePath = null;
            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('campaigns', 'public');
            }

            $campaign = Campaign::create([
                'created_by' => Auth::id(),
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . Str::random(5),
                'description' => $request->description,
                'content' => $request->content,
                'category' => $request->category,
                'target_audience' => $request->target_audience,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'featured_image' => $imagePath,
                'is_featured' => $request->boolean('is_featured'),
                'status' => $request->status ?? 'draft',
            ]);

            return $this->success($campaign, 'Kampanye berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Update a campaign (government only).
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:1000',
                'content' => 'sometimes|string|min:100',
                'category' => 'sometimes|string',
                'target_audience' => 'sometimes|string',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'is_featured' => 'boolean',
                'status' => 'sometimes|in:draft,active,ended,cancelled',
            ]);

            if ($request->hasFile('featured_image')) {
                $imagePath = $request->file('featured_image')->store('campaigns', 'public');
                $campaign->featured_image = $imagePath;
            }

            $campaign->fill($request->only([
                'title', 'description', 'content', 'category',
                'target_audience', 'start_date', 'end_date', 'status'
            ]));

            if ($request->has('is_featured')) {
                $campaign->is_featured = $request->boolean('is_featured');
            }

            $campaign->save();

            return $this->success($campaign->fresh(), 'Kampanye berhasil diperbarui.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Delete a campaign (government only).
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return $this->success(null, 'Kampanye berhasil dihapus.');
    }

    /**
     * Publish a campaign.
     */
    public function publish(Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== 'draft') {
            return $this->error('Hanya kampanye draft yang dapat dipublikasikan.', 400);
        }

        $campaign->update(['status' => 'active']);

        return $this->success($campaign->fresh(), 'Kampanye berhasil dipublikasikan.');
    }
}
