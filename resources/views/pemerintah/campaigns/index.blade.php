@extends('layouts.dashboard')

@section('title', 'Kelola Kampanye Edukasi')

@section('sidebar')
    @include('pemerintah.partials.sidebar')
@endsection

@section('page-title', 'Kampanye Edukasi')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Total Kampanye</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
                <i class="fas fa-bullhorn fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Aktif</h6>
                    <h3 class="mb-0">{{ $stats['active'] }}</h3>
                </div>
                <i class="fas fa-check-circle fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Draft</h6>
                    <h3 class="mb-0">{{ $stats['draft'] }}</h3>
                </div>
                <i class="fas fa-edit fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Berakhir</h6>
                    <h3 class="mb-0">{{ $stats['ended'] }}</h3>
                </div>
                <i class="fas fa-flag-checkered fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Kampanye</h5>
        <a href="{{ route('pemerintah.campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Buat Kampanye
        </a>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <form action="" method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Berakhir</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari kampanye..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kampanye</th>
                        <th>Kategori</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($campaign->featured_image)
                                    <img src="{{ asset('storage/' . $campaign->featured_image) }}"
                                         alt="{{ $campaign->title }}"
                                         class="rounded me-3"
                                         style="width: 60px; height: 40px; object-fit: cover;">
                                @endif
                                <div>
                                    <strong>{{ $campaign->title }}</strong>
                                    @if($campaign->is_featured)
                                        <span class="badge bg-warning ms-1">Featured</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ Str::limit($campaign->description, 50) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $campaign->category }}</td>
                        <td>
                            <small>
                                {{ $campaign->start_date->format('d M Y') }} -
                                {{ $campaign->end_date->format('d M Y') }}
                            </small>
                        </td>
                        <td>{!! $campaign->status_badge !!}</td>
                        <td>{{ number_format($campaign->views_count) }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pemerintah.campaigns.show', $campaign) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('pemerintah.campaigns.edit', $campaign) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($campaign->status === 'draft')
                                    <form action="{{ route('pemerintah.campaigns.publish', $campaign) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="fas fa-rocket"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada kampanye</p>
                            <a href="{{ route('pemerintah.campaigns.create') }}" class="btn btn-primary">
                                Buat Kampanye Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $campaigns->links() }}
    </div>
</div>
@endsection
