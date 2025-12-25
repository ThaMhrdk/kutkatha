<?php

namespace App\Http\Controllers\Api;

use App\Models\Psikolog;
use App\Notifications\PsikologVerifiedNotification;
use App\Notifications\PsikologRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PsikologController extends ApiController
{
    /**
     * Get list of verified psikologs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Psikolog::with('user')
            ->where('verification_status', 'verified');

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', '%' . $request->specialization . '%');
        }

        // Filter by experience
        if ($request->filled('min_experience')) {
            $query->where('experience_years', '>=', $request->min_experience);
        }

        // Filter by max fee
        if ($request->filled('max_fee')) {
            $query->where('consultation_fee', '<=', $request->max_fee);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'average_rating');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'fee') {
            $query->orderBy('consultation_fee', $sortOrder);
        } elseif ($sortBy === 'experience') {
            $query->orderBy('experience_years', $sortOrder);
        } else {
            $query->orderByDesc('average_rating');
        }

        $psikologs = $query->paginate($request->get('per_page', 10));

        return $this->paginated($psikologs, 'Daftar psikolog berhasil diambil.');
    }

    /**
     * Get psikolog detail.
     */
    public function show(Psikolog $psikolog): JsonResponse
    {
        $psikolog->load(['user', 'schedules' => function ($q) {
            $q->where('date', '>=', now()->toDateString())
              ->where('is_available', true)
              ->orderBy('date')
              ->orderBy('start_time');
        }]);

        // Calculate stats
        $psikolog->total_consultations = $psikolog->getTotalConsultationsAttribute();

        return $this->success($psikolog, 'Detail psikolog berhasil diambil.');
    }

    /**
     * Get pending psikologs for admin verification.
     */
    public function pending(Request $request): JsonResponse
    {
        $psikologs = Psikolog::with('user')
            ->where('verification_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($psikologs, 'Daftar psikolog pending berhasil diambil.');
    }

    /**
     * Verify a psikolog (Admin only).
     */
    public function verify(Psikolog $psikolog): JsonResponse
    {
        if ($psikolog->verification_status === 'verified') {
            return $this->error('Psikolog sudah terverifikasi.', 400);
        }

        $psikolog->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        // Send notification email
        try {
            $psikolog->user->notify(new PsikologVerifiedNotification());
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send verification notification: ' . $e->getMessage());
        }

        return $this->success($psikolog->fresh()->load('user'), 'Psikolog berhasil diverifikasi.');
    }

    /**
     * Reject a psikolog (Admin only).
     */
    public function reject(Request $request, Psikolog $psikolog): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'required|string|min:10',
            ]);

            $psikolog->update([
                'verification_status' => 'rejected',
            ]);

            // Send notification email with rejection reason
            try {
                $psikolog->user->notify(new PsikologRejectedNotification($request->reason));
            } catch (\Exception $e) {
                \Log::error('Failed to send rejection notification: ' . $e->getMessage());
            }

            return $this->success($psikolog->fresh()->load('user'), 'Psikolog ditolak.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }
}
