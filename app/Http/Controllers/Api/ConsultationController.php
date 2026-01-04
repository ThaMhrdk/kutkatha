<?php

namespace App\Http\Controllers\Api;

use App\Models\Consultation;
use App\Models\Booking;
use App\Models\Feedback;
use App\Notifications\ConsultationCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConsultationController extends ApiController
{
    /**
     * Get user's consultations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Consultation::with(['booking.schedule.psikolog.user', 'feedback'])
            ->whereHas('booking', function ($q) {
                $q->where('user_id', Auth::id());
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consultations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($consultations, 'Daftar konsultasi berhasil diambil.');
    }

    /**
     * Get consultation detail.
     */
    public function show(Consultation $consultation): JsonResponse
    {
        // Check ownership (user or psikolog)
        $userId = Auth::id();
        $psikolog = Auth::user()->psikolog;

        $isOwner = (int) $consultation->booking->user_id === (int) $userId;
        $isPsikolog = $psikolog && (int) $consultation->booking->schedule->psikolog_id === (int) $psikolog->id;

        if (!$isOwner && !$isPsikolog) {
            return $this->error('Unauthorized.', 403);
        }

        $consultation->load([
            'booking.user',
            'booking.schedule.psikolog.user',
            'feedback',
            'chatMessages.sender'
        ]);

        return $this->success($consultation, 'Detail konsultasi berhasil diambil.');
    }

    /**
     * Get psikolog's consultations.
     */
    public function psikologConsultations(Request $request): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if (!$psikolog) {
            return $this->error('Profil psikolog tidak ditemukan.', 404);
        }

        $query = Consultation::with(['booking.user', 'booking.schedule'])
            ->whereHas('booking.schedule', function ($q) use ($psikolog) {
                $q->where('psikolog_id', $psikolog->id);
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consultations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($consultations, 'Daftar konsultasi berhasil diambil.');
    }

    /**
     * Start a consultation (psikolog only).
     */
    public function start(Booking $booking): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if ((int) $booking->schedule->psikolog_id !== (int) $psikolog->id) {
            return $this->error('Unauthorized.', 403);
        }

        if ($booking->status !== 'confirmed') {
            return $this->error('Booking belum dikonfirmasi.', 400);
        }

        if ($booking->consultation) {
            return $this->error('Konsultasi sudah dimulai.', 400);
        }

        $consultation = Consultation::create([
            'booking_id' => $booking->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this->success(
            $consultation->load('booking.user'),
            'Konsultasi berhasil dimulai.',
            201
        );
    }

    /**
     * Complete a consultation and send result (psikolog only).
     */
    public function complete(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if ((int) $consultation->booking->schedule->psikolog_id !== (int) $psikolog->id) {
                return $this->error('Unauthorized.', 403);
            }

            if ($consultation->status === 'completed') {
                return $this->error('Konsultasi sudah selesai.', 400);
            }

            $request->validate([
                'summary' => 'required|string|min:50',
                'diagnosis' => 'nullable|string',
                'recommendation' => 'required|string|min:20',
                'follow_up_notes' => 'nullable|string',
                'next_session_date' => 'nullable|date|after:today',
            ]);

            $consultation->update([
                'summary' => $request->summary,
                'diagnosis' => $request->diagnosis,
                'recommendation' => $request->recommendation,
                'follow_up_notes' => $request->follow_up_notes,
                'next_session_date' => $request->next_session_date,
                'status' => 'completed',
                'ended_at' => now(),
            ]);

            // Update booking status
            $consultation->booking->update(['status' => 'completed']);

            // Send notification to user
            try {
                $consultation->booking->user->notify(
                    new ConsultationCompletedNotification($consultation)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send completion notification: ' . $e->getMessage());
            }

            return $this->success(
                $consultation->fresh()->load('booking.user'),
                'Konsultasi berhasil diselesaikan.'
            );

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Update consultation notes (psikolog only).
     */
    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if ((int) $consultation->booking->schedule->psikolog_id !== (int) $psikolog->id) {
                return $this->error('Unauthorized.', 403);
            }

            $request->validate([
                'summary' => 'sometimes|string',
                'diagnosis' => 'nullable|string',
                'recommendation' => 'sometimes|string',
                'follow_up_notes' => 'nullable|string',
                'next_session_date' => 'nullable|date',
            ]);

            $consultation->update($request->only([
                'summary', 'diagnosis', 'recommendation',
                'follow_up_notes', 'next_session_date'
            ]));

            return $this->success($consultation->fresh(), 'Konsultasi berhasil diperbarui.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Store feedback for a consultation (user only).
     */
    public function storeFeedback(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            if ((int) $consultation->booking->user_id !== (int) Auth::id()) {
                return $this->error('Unauthorized.', 403);
            }

            if ($consultation->status !== 'completed') {
                return $this->error('Konsultasi belum selesai.', 400);
            }

            if ($consultation->feedback) {
                return $this->error('Feedback sudah diberikan.', 400);
            }

            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'is_anonymous' => 'boolean',
            ]);

            $feedback = Feedback::create([
                'consultation_id' => $consultation->id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_anonymous' => $request->boolean('is_anonymous'),
            ]);

            return $this->success($feedback, 'Feedback berhasil diberikan.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }
}
