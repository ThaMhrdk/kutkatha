@extends('layouts.dashboard')

@section('title', $campaign->title)

@section('sidebar')
    @include('pemerintah.partials.sidebar')
@endsection

@section('page-title', 'Detail Kampanye')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            @if($campaign->featured_image)
                <img src="{{ asset('storage/' . $campaign->featured_image) }}"
                     class="card-img-top"
                     alt="{{ $campaign->title }}"
                     style="height: 300px; object-fit: cover;">
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="card-title mb-1">{{ $campaign->title }}</h4>
                        {!! $campaign->status_badge !!}
                        @if($campaign->is_featured)
                            <span class="badge bg-warning">Featured</span>
                        @endif
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('pemerintah.campaigns.edit', $campaign) }}">
                                    <i class="fas fa-edit me-2"></i> Edit
                                </a>
                            </li>
                            @if($campaign->status === 'draft')
                                <li>
                                    <form action="{{ route('pemerintah.campaigns.publish', $campaign) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-rocket me-2"></i> Publikasikan
                                        </button>
                                    </form>
                                </li>
                            @endif
                            @if($campaign->status === 'active')
                                <li>
                                    <form action="{{ route('pemerintah.campaigns.end', $campaign) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="fas fa-stop me-2"></i> Akhiri Kampanye
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('pemerintah.campaigns.destroy', $campaign) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus kampanye ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash me-2"></i> Hapus
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="lead text-muted">{{ $campaign->description }}</p>

                <hr>

                <div class="campaign-content">
                    {!! nl2br(e($campaign->content)) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Kampanye</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>{!! $campaign->status_badge !!}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>{{ $campaign->category }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Target Audiens</td>
                        <td>{{ $campaign->target_audience }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Periode</td>
                        <td>
                            {{ $campaign->start_date->format('d M Y') }} -
                            {{ $campaign->end_date->format('d M Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Durasi</td>
                        <td>{{ $campaign->start_date->diffInDays($campaign->end_date) }} hari</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Views</td>
                        <td>{{ number_format($campaign->views_count) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat</td>
                        <td>{{ $campaign->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pembuat</td>
                        <td>{{ $campaign->creator->name }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Statistik</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Views</span>
                    <h4 class="mb-0">{{ number_format($campaign->views_count) }}</h4>
                </div>
                @if($campaign->isActive())
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Kampanye sedang berjalan
                    </div>
                @elseif($campaign->status === 'draft')
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Kampanye belum dipublikasikan
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-flag-checkered me-2"></i>
                        Kampanye telah berakhir
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('pemerintah.campaigns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>
@endsection
