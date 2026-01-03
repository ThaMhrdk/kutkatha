@extends('layouts.dashboard')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Konsultasi</h6>
                    <h2 class="mb-0">{{ $totalConsultations }}</h2>
                </div>
                <i class="fas fa-comments fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Pendapatan</h6>
                    <h2 class="mb-0">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h2>
                </div>
                <i class="fas fa-money-bill fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Rata-rata Rating</h6>
                    <h2 class="mb-0">{{ $averageRating }}</h2>
                </div>
                <i class="fas fa-star fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">User Aktif</h6>
                    <h2 class="mb-0">{{ $activeUsers }}</h2>
                </div>
                <i class="fas fa-users fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Generate Laporan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.report.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Judul Laporan</label>
                <input type="text" name="title" class="form-control" placeholder="Laporan Bulanan Januari 2026" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jenis Laporan</label>
                <select name="report_type" class="form-select" required>
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">Triwulan</option>
                    <option value="annual">Tahunan</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="period_start" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="period_end" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-file-export me-2"></i>Generate
                </button>
            </div>
            <div class="col-12">
                <label class="form-label">Ringkasan (Opsional)</label>
                <textarea name="summary" class="form-control" rows="2" placeholder="Catatan tambahan untuk laporan..."></textarea>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Laporan -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Daftar Laporan</h5>
    </div>
    <div class="card-body">
        @if($reports->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Konsultasi</th>
                        <th>User Baru</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td>
                            <strong>{{ $report->title }}</strong>
                            <br><small class="text-muted">Dibuat: {{ $report->created_at->format('d M Y') }}</small>
                        </td>
                        <td>{{ $report->report_type_name }}</td>
                        <td>{{ $report->period_start->format('d M Y') }} - {{ $report->period_end->format('d M Y') }}</td>
                        <td>{{ $report->total_consultations }}</td>
                        <td>{{ $report->total_users }}</td>
                        <td>
                            @if($report->status == 'sent')
                                <span class="badge bg-success">Terkirim</span>
                                <br><small class="text-muted">{{ $report->sent_at->format('d M Y H:i') }}</small>
                            @else
                                <span class="badge bg-warning">Draft</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.report.show', $report) }}" class="btn btn-sm btn-outline-primary" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($report->status == 'draft')
                            <form action="{{ route('admin.report.send', $report) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Kirim laporan ini ke Pemerintah?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Kirim ke Pemerintah">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $reports->links() }}
        @else
        <div class="text-center py-4">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada laporan. Buat laporan pertama di atas.</p>
        </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Statistik Konsultasi per Bulan</h5>
            </div>
            <div class="card-body">
                <canvas id="consultationChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Top Psikolog</h5>
            </div>
            <div class="card-body">
                @if(isset($topPsikologs) && $topPsikologs->count() > 0)
                    @foreach($topPsikologs as $i => $psikolog)
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $i == 0 ? 'warning' : ($i == 1 ? 'secondary' : 'info') }} me-2">{{ $i + 1 }}</span>
                            <img src="{{ $psikolog->user->photo_url }}" alt="" class="rounded-circle me-2"
                                 style="width: 32px; height: 32px; object-fit: cover;">
                            <span>{{ $psikolog->user->name }}</span>
                        </div>
                        <small class="text-muted">{{ $psikolog->consultations_count }} sesi</small>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center mb-0">Tidak ada data</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('consultationChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
        datasets: [{
            label: 'Konsultasi',
            data: {!! json_encode($chartData ?? [12, 19, 15, 25, 22, 30]) !!},
            backgroundColor: '#4A90A4'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endpush
@endsection
