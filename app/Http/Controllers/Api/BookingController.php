<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingRejectedNotification;
use App\Notifications\NewBookingNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends ApiController
{
    /**
     * Get user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['schedule.psikolog.user', 'payment'])
            ->where('user_id', Auth::id());

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('date', '>=', $request->from_date);
            });
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($bookings, 'Daftar booking berhasil diambil.');
    }

    /**
     * Create a new booking.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'complaint' => 'required|string|min:10|max:1000',
                'notes' => 'nullable|string|max:500',
            ]);

            $schedule = Schedule::findOrFail($request->schedule_id);

            // Check if schedule is available
            if (!$schedule->is_available) {
                return $this->error('Jadwal tidak tersedia.', 400);
            }

            // Check if already booked
            if ($schedule->isBooked()) {
                return $this->error('Jadwal sudah di-booking.', 400);
            }

            // Check if psikolog is verified
            if (!$schedule->psikolog->isVerified()) {
                return $this->error('Psikolog belum terverifikasi.', 400);
            }

            DB::beginTransaction();

            try {
                // Create booking
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'schedule_id' => $schedule->id,
                    'complaint' => $request->complaint,
                    'notes' => $request->notes,
                    'status' => 'pending',
                ]);

                // Create payment record
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $schedule->psikolog->consultation_fee,
                    'payment_method' => 'transfer',
                    'status' => 'pending',
                ]);

                // Update schedule availability
                $schedule->update(['is_available' => false]);

                DB::commit();

                // Send notification to psikolog
                try {
                    $schedule->psikolog->user->notify(new NewBookingNotification($booking));
                } catch (\Exception $e) {
                    \Log::error('Failed to send booking notification: ' . $e->getMessage());
                }

                $booking->load(['schedule.psikolog.user', 'payment']);

                return $this->success($booking, 'Booking berhasil dibuat.', 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get booking detail.
     */
    public function show(Booking $booking): JsonResponse
    {
        // Check ownership
        if ($booking->user_id !== Auth::id()) {
            return $this->error('Unauthorized.', 403);
        }

        $booking->load(['schedule.psikolog.user', 'payment', 'consultation']);

        return $this->success($booking, 'Detail booking berhasil diambil.');
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        try {
            if ($booking->user_id !== Auth::id()) {
                return $this->error('Unauthorized.', 403);
            }

            if (!$booking->canBeCancelled()) {
                return $this->error('Booking tidak dapat dibatalkan.', 400);
            }

            $request->validate([
                'reason' => 'required|string|min:10',
            ]);

            DB::beginTransaction();

            try {
                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancel_reason' => $request->reason,
                ]);

                // Make schedule available again
                $booking->schedule->update(['is_available' => true]);

                // Update payment if exists
                if ($booking->payment && $booking->payment->status === 'paid') {
                    $booking->payment->update(['status' => 'refunded']);
                } else if ($booking->payment) {
                    $booking->payment->update(['status' => 'cancelled']);
                }

                DB::commit();

                return $this->success($booking->fresh()->load('schedule.psikolog.user'), 'Booking berhasil dibatalkan.');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Reschedule a booking.
     */
    public function reschedule(Request $request, Booking $booking): JsonResponse
    {
        try {
            if ($booking->user_id !== Auth::id()) {
                return $this->error('Unauthorized.', 403);
            }

            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                return $this->error('Booking tidak dapat dijadwalkan ulang.', 400);
            }

            $request->validate([
                'new_schedule_id' => 'required|exists:schedules,id',
            ]);

            $newSchedule = Schedule::findOrFail($request->new_schedule_id);

            // Validate new schedule
            if (!$newSchedule->is_available || $newSchedule->isBooked()) {
                return $this->error('Jadwal baru tidak tersedia.', 400);
            }

            // Must be same psikolog
            if ($newSchedule->psikolog_id !== $booking->schedule->psikolog_id) {
                return $this->error('Jadwal baru harus dengan psikolog yang sama.', 400);
            }

            DB::beginTransaction();

            try {
                // Release old schedule
                $booking->schedule->update(['is_available' => true]);

                // Update booking
                $booking->update([
                    'schedule_id' => $newSchedule->id,
                    'status' => 'rescheduled',
                ]);

                // Reserve new schedule
                $newSchedule->update(['is_available' => false]);

                DB::commit();

                return $this->success(
                    $booking->fresh()->load('schedule.psikolog.user'),
                    'Jadwal berhasil diubah.'
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Process payment for booking.
     */
    public function processPayment(Request $request, Booking $booking): JsonResponse
    {
        try {
            if ($booking->user_id !== Auth::id()) {
                return $this->error('Unauthorized.', 403);
            }

            if (!$booking->payment || $booking->payment->status !== 'pending') {
                return $this->error('Tidak ada pembayaran yang pending.', 400);
            }

            $request->validate([
                'payment_method' => 'required|in:transfer,ewallet',
                'proof_of_payment' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $proofPath = $request->file('proof_of_payment')->store('payment-proofs', 'public');

            $booking->payment->update([
                'payment_method' => $request->payment_method,
                'proof_of_payment' => $proofPath,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $this->success($booking->fresh()->load('payment'), 'Pembayaran berhasil diproses.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get psikolog's bookings (for psikolog role).
     */
    public function psikologBookings(Request $request): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if (!$psikolog) {
            return $this->error('Profil psikolog tidak ditemukan.', 404);
        }

        $query = Booking::with(['user', 'schedule', 'payment'])
            ->whereHas('schedule', function ($q) use ($psikolog) {
                $q->where('psikolog_id', $psikolog->id);
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($bookings, 'Daftar booking berhasil diambil.');
    }

    /**
     * Confirm a booking (psikolog only).
     */
    public function confirm(Booking $booking): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if ($booking->schedule->psikolog_id !== $psikolog->id) {
            return $this->error('Unauthorized.', 403);
        }

        if ($booking->status !== 'pending') {
            return $this->error('Booking tidak dalam status pending.', 400);
        }

        // Check if payment is done
        if (!$booking->isPaid()) {
            return $this->error('Pembayaran belum dilakukan.', 400);
        }

        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Send notification to user
        try {
            $booking->user->notify(new BookingConfirmedNotification($booking));
        } catch (\Exception $e) {
            \Log::error('Failed to send confirmation notification: ' . $e->getMessage());
        }

        return $this->success($booking->fresh()->load('user'), 'Booking berhasil dikonfirmasi.');
    }

    /**
     * Reject a booking (psikolog only).
     */
    public function reject(Request $request, Booking $booking): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if ($booking->schedule->psikolog_id !== $psikolog->id) {
                return $this->error('Unauthorized.', 403);
            }

            if ($booking->status !== 'pending') {
                return $this->error('Booking tidak dalam status pending.', 400);
            }

            $request->validate([
                'reason' => 'required|string|min:10',
            ]);

            DB::beginTransaction();

            try {
                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancel_reason' => $request->reason,
                ]);

                // Make schedule available again
                $booking->schedule->update(['is_available' => true]);

                // Refund if paid
                if ($booking->payment && $booking->payment->status === 'paid') {
                    $booking->payment->update(['status' => 'refunded']);
                }

                DB::commit();

                // Send notification to user
                try {
                    $booking->user->notify(new BookingRejectedNotification($booking, $request->reason));
                } catch (\Exception $e) {
                    \Log::error('Failed to send rejection notification: ' . $e->getMessage());
                }

                return $this->success($booking->fresh()->load('user'), 'Booking ditolak.');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }
}
