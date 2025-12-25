<div class="nav-section">Menu Utama</div>
<a href="{{ route('home') }}" class="nav-link">
    <i class="fas fa-home"></i> Beranda
</a>
<a href="{{ route('pemerintah.dashboard') }}" class="nav-link {{ request()->routeIs('pemerintah.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
<a href="{{ route('pemerintah.reports') }}" class="nav-link {{ request()->routeIs('pemerintah.reports') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Laporan
</a>
<a href="{{ route('pemerintah.statistics') }}" class="nav-link {{ request()->routeIs('pemerintah.statistics') ? 'active' : '' }}">
    <i class="fas fa-chart-pie"></i> Statistik
</a>
<a href="{{ route('pemerintah.campaigns.index') }}" class="nav-link {{ request()->routeIs('pemerintah.campaigns.*') ? 'active' : '' }}">
    <i class="fas fa-bullhorn"></i> Kampanye Edukasi
</a>

<div class="nav-section mt-3">Akun</div>
<a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> Pengaturan
</a>
