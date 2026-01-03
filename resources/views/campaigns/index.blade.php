@extends('layouts.app')

@section('title', 'Kampanye Edukasi')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Kampanye Edukasi Kesehatan Mental</h1>
        <p class="text-muted lead">Program dan kampanye dari Dinas Kesehatan Kutai Kartanegara</p>
    </div>

    @if($featuredCampaigns->count() > 0)
    <div class="mb-5">
        <h4 class="mb-4"><i class="fas fa-star text-warning me-2"></i>Kampanye Unggulan</h4>
        <div class="row g-4">
            @foreach($featuredCampaigns as $campaign)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-warning">
                    @if($campaign->featured_image)
                    <img src="{{ asset('storage/' . $campaign->featured_image) }}" class="card-img-top" alt="{{ $campaign->title }}" style="height: 200px; object-fit: cover;">
                    @else
                    <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-bullhorn fa-4x text-white opacity-50"></i>
                    </div>
                    @endif
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Featured</span>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                        <h5 class="card-title">{{ $campaign->title }}</h5>
                        <p class="card-text text-muted small">{{ Str::limit($campaign->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $campaign->start_date->format('d M') }} - {{ $campaign->end_date->format('d M Y') }}
                            </small>
                            <a href="{{ route('campaigns.public.show', $campaign) }}" class="btn btn-sm btn-primary">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <h4 class="mb-4"><i class="fas fa-bullhorn me-2"></i>Semua Kampanye</h4>

    @if($campaigns->count() > 0)
    <div class="row g-4">
        @foreach($campaigns as $campaign)
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                @if($campaign->featured_image)
                <img src="{{ asset('storage/' . $campaign->featured_image) }}" class="card-img-top" alt="{{ $campaign->title }}" style="height: 180px; object-fit: cover;">
                @else
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 180px;">
                    <i class="fas fa-bullhorn fa-3x text-white opacity-50"></i>
                </div>
                @endif
                <div class="card-body">
                    <div class="mb-2">
                        @if($campaign->is_featured)
                        <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Featured</span>
                        @endif
                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $campaign->category)) }}</span>
                    </div>
                    <h5 class="card-title">{{ $campaign->title }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($campaign->description, 80) }}</p>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-eye me-1"></i>{{ $campaign->views_count }} views
                        </small>
                        <a href="{{ route('campaigns.public.show', $campaign) }}" class="btn btn-sm btn-outline-primary">
                            Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $campaigns->links() }}
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">Belum ada kampanye aktif</h5>
        <p class="text-muted">Kampanye dari Dinas Kesehatan akan ditampilkan di sini</p>
    </div>
    @endif
</div>
@endsection
