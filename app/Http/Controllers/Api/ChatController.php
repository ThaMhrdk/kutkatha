<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessage;
use App\Models\Consultation;
use App\Models\Booking;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChatController extends ApiController
{
    /**
     * Get or create consultation for a booking, then get messages.
     */
    public function getMessagesByBooking(Request $request, Booking $booking): JsonResponse
    {
        // Verify access
        if (!$this->canAccessBooking($booking)) {
            return $this->error('Unauthorized.', 403);
        }

        // Get existing consultation
        $consultation = $booking->consultation;

        // If no consultation exists, check if booking is ready for chat
        if (!$consultation) {
            // Check if booking is confirmed and paid
            if ($booking->status !== 'confirmed' || !$booking->isPaid()) {
                return $this->error('Booking belum dikonfirmasi atau belum dibayar.', 400);
            }

            // Create new consultation
            $consultation = Consultation::create([
                'booking_id' => $booking->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $query = $consultation->chatMessages()->with('sender');

        if ($request->filled('after_id')) {
            $query->where('id', '>', $request->after_id);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return $this->success([
            'consultation' => $consultation->load('booking.schedule.psikolog.user'),
            'messages' => $messages,
            'last_id' => $messages->last()?->id,
            'timestamp' => now()->toIso8601String(),
        ], 'Pesan berhasil diambil.');
    }

    /**
     * Send message by booking (auto-create consultation if needed).
     */
    public function sendMessageByBooking(Request $request, Booking $booking): JsonResponse
    {
        try {
            // Verify access
            if (!$this->canAccessBooking($booking)) {
                return $this->error('Unauthorized.', 403);
            }

            // Get existing consultation
            $consultation = $booking->consultation;

            // If no consultation, check if we can create one
            if (!$consultation) {
                if ($booking->status !== 'confirmed' || !$booking->isPaid()) {
                    return $this->error('Booking belum dikonfirmasi atau belum dibayar.', 400);
                }

                $consultation = Consultation::create([
                    'booking_id' => $booking->id,
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }

            // Check if consultation is still active (allow sending messages)
            if ($consultation->status === 'completed') {
                return $this->error('Konsultasi sudah selesai. Tidak dapat mengirim pesan.', 400);
            }

            $request->validate([
                'message' => 'required|string|max:2000',
            ]);

            // Create message
            $message = ChatMessage::create([
                'consultation_id' => $consultation->id,
                'sender_id' => Auth::id(),
                'message' => $request->message,
            ]);

            $message->load('sender');

            return $this->success([
                'consultation_id' => $consultation->id,
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ], 'Pesan berhasil dikirim.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Check if current user can access the booking.
     */
    private function canAccessBooking(Booking $booking): bool
    {
        $userId = Auth::id();
        $psikolog = Auth::user()->psikolog;

        // User owns the booking
        if ($booking->user_id === $userId) {
            return true;
        }

        // Psikolog owns the schedule
        if ($psikolog && $booking->schedule->psikolog_id === $psikolog->id) {
            return true;
        }

        return false;
    }

    /**
     * Get chat messages for a consultation (async polling).
     */
    public function getMessages(Request $request, Consultation $consultation): JsonResponse
    {
        // Verify access
        if (!$this->canAccessConsultation($consultation)) {
            return $this->error('Unauthorized.', 403);
        }

        $query = $consultation->chatMessages()->with('sender');

        // For async polling - only get messages after a certain ID
        if ($request->filled('after_id')) {
            $query->where('id', '>', $request->after_id);
        }

        // For async polling - only get messages after a certain timestamp
        if ($request->filled('after_time')) {
            $query->where('created_at', '>', $request->after_time);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return $this->success([
            'messages' => $messages,
            'last_id' => $messages->last()?->id,
            'timestamp' => now()->toIso8601String(),
        ], 'Pesan berhasil diambil.');
    }

    /**
     * Send a chat message (async).
     */
    public function sendMessage(Request $request, Consultation $consultation): JsonResponse
    {
        try {
            // Verify access
            if (!$this->canAccessConsultation($consultation)) {
                return $this->error('Unauthorized.', 403);
            }

            // Check if consultation is active
            if (!in_array($consultation->status, ['in_progress', 'pending'])) {
                return $this->error('Konsultasi tidak aktif.', 400);
            }

            $request->validate([
                'message' => 'required|string|max:2000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('chat-attachments', 'public');
            }

            $message = ChatMessage::create([
                'consultation_id' => $consultation->id,
                'sender_id' => Auth::id(),
                'message' => $request->message,
                'attachment' => $attachmentPath,
            ]);

            $message->load('sender');

            return $this->success([
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ], 'Pesan berhasil dikirim.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Check if current user can access the consultation.
     */
    private function canAccessConsultation(Consultation $consultation): bool
    {
        $userId = Auth::id();
        $psikolog = Auth::user()->psikolog;

        // User owns the booking
        if ($consultation->booking->user_id === $userId) {
            return true;
        }

        // Psikolog owns the schedule
        if ($psikolog && $consultation->booking->schedule->psikolog_id === $psikolog->id) {
            return true;
        }

        return false;
    }
}
