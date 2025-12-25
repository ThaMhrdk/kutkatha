@extends('layouts.dashboard')

@section('title', 'Buat Kampanye Baru')

@section('sidebar')
    <div class="nav-section">Menu</div>
    <a href="{{ route('pemerintah.dashboard') }}" class="nav-link">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="{{ route('pemerintah.reports') }}" class="nav-link">
        <i class="fas fa-file-alt"></i> Laporan
    </a>
    <a href="{{ route('pemerintah.statistics') }}" class="nav-link">
        <i class="fas fa-chart-bar"></i> Statistik
    </a>
    <a href="{{ route('pemerintah.campaigns.index') }}" class="nav-link active">
        <i class="fas fa-bullhorn"></i> Kampanye Edukasi
    </a>
@endsection

@section('page-title', 'Buat Kampanye Baru')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Form Kampanye Edukasi</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('pemerintah.campaigns.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Kampanye <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror"
                                    id="category" name="category" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="target_audience" class="form-label">Target Audiens <span class="text-danger">*</span></label>
                            <select class="form-select @error('target_audience') is-invalid @enderror"
                                    id="target_audience" name="target_audience" required>
                                @foreach($targetAudiences as $key => $label)
                                    <option value="{{ $key }}" {{ old('target_audience') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('target_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Konten Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Gambar Unggulan</label>
                        <input type="file" class="form-control @error('featured_image') is-invalid @enderror"
                               id="featured_image" name="featured_image" accept="image/*">
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Tandai sebagai Kampanye Unggulan
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pemerintah.campaigns.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Batal
                        </a>
                        <div>
                            <button type="submit" name="status" value="draft" class="btn btn-outline-primary">
                                <i class="fas fa-save me-1"></i> Simpan Draft
                            </button>
                            <button type="submit" name="status" value="active" class="btn btn-primary">
                                <i class="fas fa-rocket me-1"></i> Publikasikan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Kampanye edukasi adalah program pemerintah untuk meningkatkan kesadaran
                    masyarakat tentang kesehatan mental.
                </p>
                <h6 class="small fw-bold">Tips:</h6>
                <ul class="small text-muted ps-3">
                    <li>Gunakan judul yang menarik dan mudah dipahami</li>
                    <li>Pilih kategori dan target audiens yang tepat</li>
                    <li>Tambahkan gambar yang relevan dan menarik</li>
                    <li>Tulis konten yang informatif dan edukatif</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
