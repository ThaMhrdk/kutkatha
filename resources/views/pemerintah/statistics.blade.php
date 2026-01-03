@extends('layouts.dashboard')

@section('title', 'Statistik')
@section('page-title', 'Statistik Kesehatan Mental')

@section('sidebar')
    @include('pemerintah.partials.sidebar')
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Pengguna</h6>
                    <h2 class="mb-0">{{ $totalUsers }}</h2>
                    <small class="opacity-75">+{{ $newUsersThisMonth }} bulan ini</small>
                </div>
                <i class="fas fa-users fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Sesi</h6>
                    <h2 class="mb-0">{{ $totalSessions }}</h2>
                    <small class="opacity-75">+{{ $newSessionsThisMonth }} bulan ini</small>
                </div>
                <i class="fas fa-calendar-check fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Artikel Edukasi</h6>
                    <h2 class="mb-0">{{ $totalArticles }}</h2>
                    <small class="opacity-75">{{ $totalArticleViews }} views</small>
                </div>
                <i class="fas fa-newspaper fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Forum Posts</h6>
                    <h2 class="mb-0">{{ $totalForumPosts }}</h2>
                    <small class="opacity-75">{{ $totalForumTopics }} topik</small>
                </div>
                <i class="fas fa-comments fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Distribusi Jenis Konsultasi</h5>
            </div>
            <div class="card-body">
                <canvas id="consultationTypeChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Kategori Forum</h5>
            </div>
            <div class="card-body">
                <canvas id="forumCategoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tren Penggunaan Platform</h5>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Rating Kepuasan</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h1 class="display-4 text-primary mb-0">{{ $averageRating }}</h1>
                    <div class="text-warning mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= round($averageRating) ? '' : 'text-muted' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted">dari {{ $totalReviews }} review</small>
                </div>

                @foreach($ratingDistribution as $rating => $count)
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2">{{ $rating }} <i class="fas fa-star text-warning"></i></span>
                    <div class="progress flex-grow-1" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $totalReviews > 0 ? ($count / $totalReviews * 100) : 0 }}%"></div>
                    </div>
                    <span class="ms-2 text-muted small">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Kinerja Psikolog</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Psikolog</th>
                        <th>Spesialisasi</th>
                        <th>Total Sesi</th>
                        <th>Rating</th>
                        <th>Tingkat Kepuasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPsikologs as $i => $psikolog)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $psikolog->user->photo_url }}" alt="" class="rounded-circle me-2"
                                     style="width: 32px; height: 32px; object-fit: cover;">
                                {{ $psikolog->user->name }}
                            </div>
                        </td>
                        <td>{{ $psikolog->specialization }}</td>
                        <td>{{ $psikolog->consultations_count }}</td>
                        <td>
                            <i class="fas fa-star text-warning"></i>
                            {{ number_format($psikolog->average_rating, 1) }}
                        </td>
                        <td>
                            <div class="progress" style="height: 8px; width: 100px;">
                                <div class="progress-bar bg-success" style="width: {{ $psikolog->satisfaction_rate ?? 85 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Usage Trend Chart with Real Data
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const yearlyData = @json($yearlyData);

const consultationData = [];
const newUsersData = [];
for (let i = 1; i <= 12; i++) {
    consultationData.push(yearlyData[i]?.consultations ?? 0);
    newUsersData.push(yearlyData[i]?.new_users ?? 0);
}

new Chart(document.getElementById('usageChart'), {
    type: 'line',
    data: {
        labels: monthNames,
        datasets: [
            {
                label: 'Konsultasi',
                data: consultationData,
                borderColor: '#4A90A4',
                backgroundColor: 'rgba(74, 144, 164, 0.1)',
                fill: true
            },
            {
                label: 'Pengguna Baru',
                data: newUsersData,
                borderColor: '#6BB5A2',
                backgroundColor: 'rgba(107, 181, 162, 0.1)',
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
    }
});

// Consultation Type Chart with Real Data
const consultationsByType = @json($consultationsByType);
const typeLabels = Object.keys(consultationsByType).length > 0
    ? Object.keys(consultationsByType).map(t => {
        const labels = {'online': 'Online', 'offline': 'Offline', 'chat': 'Chat'};
        return labels[t] || t;
    })
    : ['Belum ada data'];
const typeData = Object.keys(consultationsByType).length > 0
    ? Object.values(consultationsByType)
    : [1];

new Chart(document.getElementById('consultationTypeChart'), {
    type: 'pie',
    data: {
        labels: typeLabels,
        datasets: [{
            data: typeData,
            backgroundColor: Object.keys(consultationsByType).length > 0
                ? ['#4A90A4', '#6BB5A2', '#F4A261']
                : ['#ccc']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Forum Category Chart with Real Data
const forumCategories = @json($forumCategories);
const forumLabels = Object.keys(forumCategories).length > 0
    ? Object.keys(forumCategories)
    : ['Belum ada data'];
const forumData = Object.keys(forumCategories).length > 0
    ? Object.values(forumCategories)
    : [0];

new Chart(document.getElementById('forumCategoryChart'), {
    type: 'bar',
    data: {
        labels: forumLabels,
        datasets: [{
            data: forumData,
            backgroundColor: '#4A90A4'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});
</script>
@endpush
@endsection
