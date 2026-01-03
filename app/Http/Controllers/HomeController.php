<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Psikolog;
use App\Models\Campaign;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $psikologs = Psikolog::with('user')
            ->where('verification_status', 'verified')
            ->take(6)
            ->get();

        $articles = Article::with('author')
            ->published()
            ->latest('published_at')
            ->take(3)
            ->get();

        $campaigns = Campaign::active()
            ->featured()
            ->latest()
            ->take(3)
            ->get();

        return view('home', compact('psikologs', 'articles', 'campaigns'));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function campaigns()
    {
        $campaigns = Campaign::active()
            ->latest()
            ->paginate(12);

        $featuredCampaigns = Campaign::active()
            ->featured()
            ->take(3)
            ->get();

        return view('campaigns.index', compact('campaigns', 'featuredCampaigns'));
    }

    public function campaignShow(Campaign $campaign)
    {
        // Increment view count
        $campaign->increment('views_count');

        $relatedCampaigns = Campaign::active()
            ->where('id', '!=', $campaign->id)
            ->where('category', $campaign->category)
            ->take(3)
            ->get();

        return view('campaigns.show', compact('campaign', 'relatedCampaigns'));
    }
}
