@extends('layouts.dashboard')

@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')

@section('sidebar')
    @if(auth()->user()->role === 'user')
        @include('user.partials.sidebar')
    @elseif(auth()->user()->role === 'psikolog')
        @include('psikolog.partials.sidebar')
    @elseif(auth()->user()->role === 'admin')
        @include('admin.partials.sidebar')
    @elseif(auth()->user()->role === 'pemerintah')
        @include('pemerintah.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Profile Photo Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Foto Profil</h5>
            </div>
            <div class="card-body text-center">
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $user->photo_url }}"
                         alt="{{ $user->name }}"
                         class="rounded-circle"
                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid var(--primary-color);"
                         id="profilePhotoPreview">
                    <label for="photoInput"
                           class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                           style="width: 40px; height: 40px; cursor: pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ ucfirst($user->role) }}</p>

                <form action="{{ route('settings.photo') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    @method('PUT')
                    <input type="file"
                           name="photo"
                           id="photoInput"
                           class="d-none"
                           accept="image/*"
                           onchange="previewPhoto(this)">
                    <div id="photoActions" class="d-none">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelPhotoChange()">
                            Batal
                        </button>
                    </div>
                </form>

                @if($user->photo)
                <form action="{{ route('settings.photo.remove') }}" method="POST" class="mt-2" id="removePhotoForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash me-1"></i> Hapus Foto
                    </button>
                </form>
                @endif

                @error('photo')
                    <div class="text-danger mt-2 small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Preferences Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Preferensi</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.preferences') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               id="darkMode"
                               name="dark_mode"
                               {{ ($user->preferences['dark_mode'] ?? false) ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <label class="form-check-label" for="darkMode">
                            <i class="fas fa-moon me-2"></i>Mode Gelap
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="emailNotifications"
                               name="email_notifications"
                               {{ ($user->preferences['email_notifications'] ?? true) ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        <label class="form-check-label" for="emailNotifications">
                            <i class="fas fa-envelope me-2"></i>Notifikasi Email
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Profile Info Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.profile') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="08xxxxxxxxxx">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address"
                                  name="address"
                                  rows="3">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Change Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password"
                                   name="current_password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let originalPhotoSrc = '';

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        originalPhotoSrc = document.getElementById('profilePhotoPreview').src;

        reader.onload = function(e) {
            document.getElementById('profilePhotoPreview').src = e.target.result;
            document.getElementById('photoActions').classList.remove('d-none');
            document.getElementById('removePhotoForm')?.classList.add('d-none');
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function cancelPhotoChange() {
    document.getElementById('profilePhotoPreview').src = originalPhotoSrc;
    document.getElementById('photoInput').value = '';
    document.getElementById('photoActions').classList.add('d-none');
    document.getElementById('removePhotoForm')?.classList.remove('d-none');
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection
