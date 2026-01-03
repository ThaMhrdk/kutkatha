@extends('layouts.dashboard')

@section('title', 'Beri Feedback')

@section('sidebar')
    @include('user.partials.sidebar')
@endsection

@section('content')
<div class="content-wrapper p-4">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.consultation.index') }}">Konsultasi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.consultation.show', $consultation) }}">Detail</a></li>
                <li class="breadcrumb-item active">Beri Feedback</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Beri Feedback Konsultasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <img src="{{ $consultation->booking->schedule->psikolog->user->photo_url }}"
                                     alt="" class="rounded-circle me-3"
                                     style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1">{{ $consultation->booking->schedule->psikolog->user->name }}</h6>
                                    <p class="mb-0 text-muted small">{{ $consultation->booking->schedule->psikolog->specialization }}</p>
                                    <small class="text-muted">{{ $consultation->booking->schedule->date->format('d M Y') }} • {{ $consultation->booking->schedule->formatted_time }}</small>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('user.consultation.store-feedback', $consultation) }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-bold">Rating <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-2">Bagaimana pengalaman konsultasi Anda?</p>
                                <div class="rating-input d-flex gap-2" id="rating-input">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-muted rating-star" data-rating="{{ $i }}"
                                       style="cursor: pointer; font-size: 2rem;"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-value" required>
                                @error('rating')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Komentar (Opsional)</label>
                                <textarea name="comment" class="form-control" rows="5"
                                          placeholder="Ceritakan pengalaman konsultasi Anda...">{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">
                                        Kirim sebagai Anonim
                                    </label>
                                </div>
                                <small class="text-muted">Nama Anda tidak akan ditampilkan jika dipilih</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Feedback
                                </button>
                                <a href="{{ route('user.consultation.show', $consultation) }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    const ratingValue = document.getElementById('rating-value');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.getAttribute('data-rating');
            ratingValue.value = rating;

            // Update star colors
            stars.forEach(s => {
                const starRating = s.getAttribute('data-rating');
                if (starRating <= rating) {
                    s.classList.remove('text-muted');
                    s.classList.add('text-warning');
                } else {
                    s.classList.remove('text-warning');
                    s.classList.add('text-muted');
                }
            });
        });

        // Hover effect
        star.addEventListener('mouseenter', function() {
            const rating = this.getAttribute('data-rating');
            stars.forEach(s => {
                const starRating = s.getAttribute('data-rating');
                if (starRating <= rating) {
                    s.classList.remove('text-muted');
                    s.classList.add('text-warning');
                }
            });
        });
    });

    // Reset on mouse leave
    const ratingInput = document.getElementById('rating-input');
    ratingInput.addEventListener('mouseleave', function() {
        const currentRating = ratingValue.value;
        stars.forEach(s => {
            const starRating = s.getAttribute('data-rating');
            if (currentRating && starRating <= currentRating) {
                s.classList.remove('text-muted');
                s.classList.add('text-warning');
            } else {
                s.classList.remove('text-warning');
                s.classList.add('text-muted');
            }
        });
    });
});
</script>
@endpush
@endsection
