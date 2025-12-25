@extends('layouts.dashboard')

@section('title', 'Laporan')
@section('page-title', 'Laporan Kesehatan Mental')

@section('sidebar')
    @include('pemerintah.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Daftar laporan yang telah dikirim ke Dinas Kesehatan</p>
    </div>
</div>

@if($reports->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Belum ada laporan</h5>
            <p class="text-muted mb-0">Laporan yang dikirim dari admin akan muncul di sini</p>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Judul Laporan</th>
                            <th>Tipe</th>
                            <th>Periode</th>
                            <th>Total Konsultasi</th>
                            <th>Dikirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->title }}</strong>
                                @if($report->summary)
                                <br><small class="text-muted">{{ Str::limit($report->summary, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($report->report_type) }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    {{ \Carbon\Carbon::parse($report->period_start)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($report->period_end)->format('d M Y') }}
                                </small>
                            </td>
                            <td>{{ $report->data['total_consultations'] ?? 0 }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $report->sent_at ? $report->sent_at->format('d M Y H:i') : '-' }}
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('pemerintah.report.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $reports->links() }}
        </div>
    </div>
@endif
@endsection
