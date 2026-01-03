@extends('layouts.app')

@section('title', $campaign->title)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('campaigns.public') }}">Kampanye</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($campaign->title, 30) }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <article class="card shadow-sm mb-4">
                @if($campaign->featured_image)
                <img src="{{ asset('storage/' . $campaign->featured_image) }}" class="card-img-top" alt="{{ $campaign->title }}" style="max-height: 400px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <div class="mb-3">
                        @if($campaign->is_featured)
                        <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Featured</span>
                        @endif
                        <span class="badge bg-success">Aktif</span>
                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $campaign->category)) }}</span>
                    </div>

                    <h1 class="card-title h2 fw-bold mb-3">{{ $campaign->title }}</h1>

                    <p class="lead text-muted mb-4">{{ $campaign->description }}</p>

                    <hr>

                    <div class="campaign-content">
                        {!! nl2br(e($campaign->content)) !!}
                    </div>
                </div>
            </article>

            <!-- Share -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-share-alt me-2"></i>Bagikan Kampanye Ini</h6>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/?text={{ urlencode($campaign->title . ' - ' . url()->current()) }}" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-primary">
                            <i class="fab fa-facebook me-1"></i>Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($campaign->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-info text-white">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Campaign Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Kampanye</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Periode</td>
                            <td>{{ $campaign->start_date->format('d M Y') }} - {{ $campaign->end_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Durasi</td>
                            <td>{{ $campaign->start_date->diffInDays($campaign->end_date) }} hari</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Target</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $campaign->target_audience)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dilihat</td>
                            <td>{{ $campaign->views_count }} kali</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Penyelenggara</td>
                            <td>{{ $campaign->creator->name ?? 'Dinas Kesehatan' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Related Campaigns -->
            @if($relatedCampaigns->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Kampanye Terkait</h6>
                </div>
                <div class="card-body">
                    @foreach($relatedCampaigns as $related)
                    <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                        @if($related->featured_image)
                        <img src="{{ asset('storage/' . $related->featured_image) }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                        <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-bullhorn text-white"></i>
                        </div>
                        @endif
                        <div>
                            <a href="{{ route('campaigns.public.show', $related) }}" class="text-decoration-none text-dark">
                                <h6 class="mb-1 small">{{ Str::limit($related->title, 40) }}</h6>
                            </a>
                            <small class="text-muted">{{ $related->start_date->format('d M Y') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
