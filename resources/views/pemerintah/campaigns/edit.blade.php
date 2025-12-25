@extends('layouts.dashboard')

@section('title', 'Edit Kampanye')

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

@section('page-title', 'Edit Kampanye')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Kampanye: {{ $campaign->title }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('pemerintah.campaigns.update', $campaign) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Kampanye <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('title') is-invalid @enderror"
                               id="title"
                               name="title"
                               value="{{ old('title', $campaign->title) }}"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category') is-invalid @enderror"
                                        id="category"
                                        name="category"
                                        required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Kesehatan Mental" {{ old('category', $campaign->category) == 'Kesehatan Mental' ? 'selected' : '' }}>Kesehatan Mental</option>
                                    <option value="Awareness" {{ old('category', $campaign->category) == 'Awareness' ? 'selected' : '' }}>Awareness</option>
                                    <option value="Pencegahan" {{ old('category', $campaign->category) == 'Pencegahan' ? 'selected' : '' }}>Pencegahan</option>
                                    <option value="Edukasi Publik" {{ old('category', $campaign->category) == 'Edukasi Publik' ? 'selected' : '' }}>Edukasi Publik</option>
                                    <option value="Workshop" {{ old('category', $campaign->category) == 'Workshop' ? 'selected' : '' }}>Workshop</option>
                                    <option value="Seminar" {{ old('category', $campaign->category) == 'Seminar' ? 'selected' : '' }}>Seminar</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audiens <span class="text-danger">*</span></label>
                                <select class="form-select @error('target_audience') is-invalid @enderror"
                                        id="target_audience"
                                        name="target_audience"
                                        required>
                                    <option value="">Pilih Target</option>
                                    <option value="Umum" {{ old('target_audience', $campaign->target_audience) == 'Umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="Remaja" {{ old('target_audience', $campaign->target_audience) == 'Remaja' ? 'selected' : '' }}>Remaja</option>
                                    <option value="Dewasa" {{ old('target_audience', $campaign->target_audience) == 'Dewasa' ? 'selected' : '' }}>Dewasa</option>
                                    <option value="Orang Tua" {{ old('target_audience', $campaign->target_audience) == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                                    <option value="Pelajar" {{ old('target_audience', $campaign->target_audience) == 'Pelajar' ? 'selected' : '' }}>Pelajar</option>
                                    <option value="Mahasiswa" {{ old('target_audience', $campaign->target_audience) == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="Pekerja" {{ old('target_audience', $campaign->target_audience) == 'Pekerja' ? 'selected' : '' }}>Pekerja</option>
                                </select>
                                @error('target_audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="3"
                                  required>{{ old('description', $campaign->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Ringkasan singkat kampanye (maksimal 255 karakter)</div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Konten Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="10"
                                  required>{{ old('content', $campaign->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               id="start_date"
                               name="start_date"
                               value="{{ old('start_date', $campaign->start_date->format('Y-m-d')) }}"
                               required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               id="end_date"
                               name="end_date"
                               value="{{ old('end_date', $campaign->end_date->format('Y-m-d')) }}"
                               required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Gambar Kampanye</label>
                        @if($campaign->featured_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $campaign->featured_image) }}"
                                     alt="Current Image"
                                     class="img-thumbnail"
                                     style="max-height: 150px;">
                                <p class="form-text mb-0">Gambar saat ini</p>
                            </div>
                        @endif
                        <input type="file"
                               class="form-control @error('featured_image') is-invalid @enderror"
                               id="featured_image"
                               name="featured_image"
                               accept="image/*">
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="is_featured"
                                   name="is_featured"
                                   value="1"
                                   {{ old('is_featured', $campaign->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Tampilkan di Halaman Utama
                            </label>
                        </div>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Info Kampanye</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>{!! $campaign->status_badge !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Views</td>
                                    <td>{{ number_format($campaign->views_count) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dibuat</td>
                                    <td>{{ $campaign->created_at->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="{{ route('pemerintah.campaigns.show', $campaign) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
