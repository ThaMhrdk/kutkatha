<?php

namespace App\Http\Controllers\Pemerintah;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns.
     */
    public function index(Request $request)
    {
        $query = Campaign::with('creator');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

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

        $campaigns = $query->orderByDesc('created_at')->paginate(15);

        // Statistics
        $stats = [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 'active')->count(),
            'draft' => Campaign::where('status', 'draft')->count(),
            'ended' => Campaign::where('status', 'ended')->count(),
        ];

        return view('pemerintah.campaigns.index', compact('campaigns', 'stats'));
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        $categories = [
            'mental_health_awareness' => 'Kesadaran Kesehatan Mental',
            'stress_management' => 'Manajemen Stres',
            'depression_prevention' => 'Pencegahan Depresi',
            'youth_mental_health' => 'Kesehatan Mental Remaja',
            'family_counseling' => 'Konseling Keluarga',
            'workplace_wellness' => 'Kesejahteraan Kerja',
            'general' => 'Umum',
        ];

        $targetAudiences = [
            'all' => 'Semua',
            'youth' => 'Remaja (13-21 tahun)',
            'adults' => 'Dewasa (22-40 tahun)',
            'elderly' => 'Lansia (40+ tahun)',
            'students' => 'Pelajar/Mahasiswa',
            'workers' => 'Pekerja',
            'parents' => 'Orang Tua',
        ];

        return view('pemerintah.campaigns.create', compact('categories', 'targetAudiences'));
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request)
    {
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

        return redirect()->route('pemerintah.campaigns.show', $campaign)
            ->with('success', 'Kampanye berhasil dibuat.');
    }

    /**
     * Display the specified campaign.
     */
    public function show(Campaign $campaign)
    {
        return view('pemerintah.campaigns.show', compact('campaign'));
    }

    /**
     * Show the form for editing the campaign.
     */
    public function edit(Campaign $campaign)
    {
        $categories = [
            'mental_health_awareness' => 'Kesadaran Kesehatan Mental',
            'stress_management' => 'Manajemen Stres',
            'depression_prevention' => 'Pencegahan Depresi',
            'youth_mental_health' => 'Kesehatan Mental Remaja',
            'family_counseling' => 'Konseling Keluarga',
            'workplace_wellness' => 'Kesejahteraan Kerja',
            'general' => 'Umum',
        ];

        $targetAudiences = [
            'all' => 'Semua',
            'youth' => 'Remaja (13-21 tahun)',
            'adults' => 'Dewasa (22-40 tahun)',
            'elderly' => 'Lansia (40+ tahun)',
            'students' => 'Pelajar/Mahasiswa',
            'workers' => 'Pekerja',
            'parents' => 'Orang Tua',
        ];

        return view('pemerintah.campaigns.edit', compact('campaign', 'categories', 'targetAudiences'));
    }

    /**
     * Update the specified campaign.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'required|string|min:100',
            'category' => 'required|string',
            'target_audience' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'is_featured' => 'boolean',
            'status' => 'in:draft,active,ended,cancelled',
        ]);

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('campaigns', 'public');
            $campaign->featured_image = $imagePath;
        }

        $campaign->fill($request->only([
            'title', 'description', 'content', 'category',
            'target_audience', 'start_date', 'end_date', 'status'
        ]));

        $campaign->is_featured = $request->boolean('is_featured');
        $campaign->save();

        return redirect()->route('pemerintah.campaigns.show', $campaign)
            ->with('success', 'Kampanye berhasil diperbarui.');
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('pemerintah.campaigns.index')
            ->with('success', 'Kampanye berhasil dihapus.');
    }

    /**
     * Publish a campaign.
     */
    public function publish(Campaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Hanya kampanye draft yang dapat dipublikasikan.');
        }

        $campaign->update(['status' => 'active']);

        return back()->with('success', 'Kampanye berhasil dipublikasikan.');
    }

    /**
     * End a campaign.
     */
    public function end(Campaign $campaign)
    {
        if ($campaign->status !== 'active') {
            return back()->with('error', 'Hanya kampanye aktif yang dapat diakhiri.');
        }

        $campaign->update(['status' => 'ended']);

        return back()->with('success', 'Kampanye berhasil diakhiri.');
    }
}
