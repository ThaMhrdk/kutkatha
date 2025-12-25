<div class="nav-section">Menu Utama</div>
<a href="{{ route('home') }}" class="nav-link">
    <i class="fas fa-home"></i> Beranda
</a>
<a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
<a href="{{ route('user.psikolog.index') }}" class="nav-link {{ request()->routeIs('user.psikolog.*') ? 'active' : '' }}">
    <i class="fas fa-user-md"></i> Cari Psikolog
</a>
<a href="{{ route('user.booking.index') }}" class="nav-link {{ request()->routeIs('user.booking.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-check"></i> Booking Saya
</a>
<a href="{{ route('user.consultation.index') }}" class="nav-link {{ request()->routeIs('user.consultation.*') ? 'active' : '' }}">
    <i class="fas fa-comments"></i> Konsultasi
</a>

<div class="nav-section mt-3">Komunitas</div>
<a href="{{ route('forum.index') }}" class="nav-link {{ request()->routeIs('forum.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> Forum Diskusi
</a>
<a href="{{ route('articles.index') }}" class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}">
    <i class="fas fa-newspaper"></i> Artikel
</a>

<div class="nav-section mt-3">Akun</div>
<a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> Pengaturan
</a>
