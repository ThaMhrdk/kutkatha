@extends('layouts.dashboard')

@section('title', 'Detail Laporan')
@section('page-title', 'Detail Laporan')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.report.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
    @if($report->status == 'draft')
    <form action="{{ route('admin.report.send', $report) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Kirim laporan ini ke Pemerintah?')">
        @csrf
        <button type="submit" class="btn btn-success">
            <i class="fas fa-paper-plane me-2"></i>Kirim ke Pemerintah
        </button>
    </form>
    @endif
</div>

<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">{{ $report->title }}</h4>
            <small class="text-muted">
                Laporan {{ $report->report_type_name }} |
                Periode: {{ $report->period_start->format('d M Y') }} - {{ $report->period_end->format('d M Y') }}
            </small>
        </div>
        <div>
            @if($report->status == 'sent')
                <span class="badge bg-success fs-6">Terkirim</span>
            @else
                <span class="badge bg-warning fs-6">Draft</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h2 class="text-primary mb-0">{{ $report->total_consultations }}</h2>
                        <small class="text-muted">Total Konsultasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h2 class="text-success mb-0">{{ $report->total_users }}</h2>
                        <small class="text-muted">User Baru</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h2 class="text-info mb-0">{{ $report->total_psikologs }}</h2>
                        <small class="text-muted">Psikolog Baru</small>
                    </div>
                </div>
            </div>
        </div>

        @if($report->statistics)
        <div class="mb-4">
            <h5><i class="fas fa-chart-bar me-2"></i>Statistik Detail</h5>
            <div class="row g-3">
                @if(isset($report->statistics['consultations_by_type']))
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>Konsultasi per Jenis</strong>
                        </div>
                        <div class="card-body">
                            @forelse($report->statistics['consultations_by_type'] as $type => $count)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ ucfirst($type) }}</span>
                                <strong>{{ $count }}</strong>
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
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>Rating Rata-rata</strong>
                        </div>
                        <div class="card-body text-center">
                            <h2 class="text-warning mb-0">
                                <i class="fas fa-star"></i> {{ number_format($report->statistics['average_rating'], 1) }}
                            </h2>
                            <small class="text-muted">dari 5.0</small>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($report->summary)
        <div class="mb-4">
            <h5><i class="fas fa-file-alt me-2"></i>Ringkasan</h5>
            <div class="card bg-light">
                <div class="card-body">
                    {!! nl2br(e($report->summary)) !!}
                </div>
            </div>
        </div>
        @endif

        <hr>

        <div class="row text-muted small">
            <div class="col-md-6">
                <p class="mb-1"><i class="fas fa-user me-2"></i>Dibuat oleh: {{ $report->creator->name ?? 'Admin' }}</p>
                <p class="mb-0"><i class="fas fa-calendar me-2"></i>Tanggal dibuat: {{ $report->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                @if($report->sent_at)
                <p class="mb-0"><i class="fas fa-paper-plane me-2"></i>Dikirim: {{ $report->sent_at->format('d M Y H:i') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
