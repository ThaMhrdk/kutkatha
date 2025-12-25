<div class="nav-section">Menu Utama</div>
<a href="{{ route('home') }}" class="nav-link">
    <i class="fas fa-home"></i> Beranda
</a>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
<a href="{{ route('admin.psikolog.index') }}" class="nav-link {{ request()->routeIs('admin.psikolog.*') ? 'active' : '' }}">
    <i class="fas fa-user-check"></i> Verifikasi Psikolog
</a>

<div class="nav-section mt-3">Manajemen</div>
<a href="{{ route('admin.report.index') }}" class="nav-link {{ request()->routeIs('admin.report.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> Laporan
</a>
<a href="{{ route('admin.forum.index') }}" class="nav-link {{ request()->routeIs('admin.forum.*') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> Forum Diskusi
</a>

<div class="nav-section mt-3">Akun</div>
<a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> Pengaturan
</a>
