@extends('layouts.dashboard')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@section('sidebar')
    @include('pemerintah.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('pemerintah.reports') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Laporan
    </a>
    <a href="#" onclick="window.print()" class="btn btn-outline-primary">
        <i class="fas fa-print me-2"></i>Cetak Laporan
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $report->title }}</h4>
                <small>
                    Laporan {{ $report->report_type_name }} |
                    Periode: {{ $report->period_start->format('d M Y') }} - {{ $report->period_end->format('d M Y') }}
                </small>
            </div>
            <span class="badge bg-light text-success fs-6">
                <i class="fas fa-check-circle me-1"></i>Diterima
            </span>
        </div>
    </div>
    <div class="card-body">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-comments fa-2x mb-2"></i>
                        <h2 class="mb-0">{{ $report->total_consultations }}</h2>
                        <small>Total Konsultasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h2 class="mb-0">{{ $report->total_users }}</h2>
                        <small>Pengguna Baru</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-md fa-2x mb-2"></i>
                        <h2 class="mb-0">{{ $report->total_psikologs }}</h2>
                        <small>Psikolog Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Detail -->
        @if($report->statistics)
        <div class="row g-4 mb-4">
            @if(isset($report->statistics['consultations_by_type']))
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Konsultasi per Jenis</h6>
                    </div>
                    <div class="card-body">
                        @forelse($report->statistics['consultations_by_type'] as $type => $count)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                @if($type == 'online')
                                    <i class="fas fa-video text-primary me-2"></i>
                                @elseif($type == 'offline')
                                    <i class="fas fa-building text-success me-2"></i>
                                @else
                                    <i class="fas fa-comment text-info me-2"></i>
                                @endif
                                <span class="text-capitalize">{{ $type }}</span>
                            </div>
                            <span class="badge bg-secondary fs-6">{{ $count }} sesi</span>
                        </div>
                        @empty
                        <p class="text-muted mb-0">Tidak ada data</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            @if(isset($report->statistics['average_rating']))
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-star me-2"></i>Rating Kepuasan</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-1 text-warning mb-2">
                            {{ number_format($report->statistics['average_rating'], 1) }}
                        </div>
                        <div class="text-muted">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($report->statistics['average_rating']))
                                    <i class="fas fa-star text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="text-muted mt-2 mb-0">dari 5.0</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Summary -->
        @if($report->summary)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Ringkasan Laporan</h6>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">{{ $report->summary }}</p>
            </div>
        </div>
        @endif

        <!-- Report Metadata -->
        <div class="border-top pt-4">
            <div class="row text-muted small">
                <div class="col-md-6">
                    <p class="mb-1">
                        <i class="fas fa-user me-2"></i>
                        Dibuat oleh: {{ $report->creator->name ?? 'Admin Kutkatha' }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Tanggal dibuat: {{ $report->created_at->format('d M Y H:i') }} WIB
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        Dikirim: {{ $report->sent_at ? $report->sent_at->format('d M Y H:i') . ' WIB' : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .sidebar, nav, footer {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
    }
}
</style>
@endpush
@endsection
