<?php

namespace App\Http\Controllers\Psikolog;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $psikolog = Auth::user()->psikolog;

        $query = Booking::with(['user', 'schedule', 'payment'])
            ->whereHas('schedule', function($q) use ($psikolog) {
                $q->where('psikolog_id', $psikolog->id);
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('psikolog.booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $psikolog = Auth::user()->psikolog;
        // Use == instead of !== for type-safe comparison (int vs string from DB)
        if ((int)$booking->schedule->psikolog_id != (int)$psikolog->id) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['user', 'schedule', 'payment', 'consultation']);

        return view('psikolog.booking.show', compact('booking'));
    }

    public function confirm(Booking $booking)
    {
        $psikolog = Auth::user()->psikolog;
        // Use == instead of !== for type-safe comparison (int vs string from DB)
        if ((int)$booking->schedule->psikolog_id != (int)$psikolog->id) {
            abort(403, 'Unauthorized');
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking tidak dapat dikonfirmasi.');
        }

        if (!$booking->isPaid()) {
            return back()->with('error', 'Pembayaran belum dilakukan.');
        }

        $booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Create consultation automatically
        $consultation = \App\Models\Consultation::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'status' => 'ongoing',
                'started_at' => now(),
            ]
        );

        // Redirect based on consultation type
        $scheduleType = $booking->schedule->type;

        if ($scheduleType === 'offline') {
            // For offline/face-to-face, go to consultation detail
            return redirect()->route('psikolog.consultation.show', $consultation)
                ->with('success', 'Booking dikonfirmasi. Silakan lakukan konsultasi tatap muka.');
        } elseif ($scheduleType === 'chat') {
            // For chat, go directly to chat
            return redirect()->route('psikolog.consultation.chat', $consultation)
                ->with('success', 'Booking dikonfirmasi. Silakan mulai konsultasi chat.');
        } else {
            // For online (video call), go to chat to send meeting link
            return redirect()->route('psikolog.consultation.chat', $consultation)
                ->with('success', 'Booking dikonfirmasi. Silakan kirim link Google Meet/Zoom ke pasien.');
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $psikolog = Auth::user()->psikolog;
        // Use == instead of !== for type-safe comparison (int vs string from DB)
        if ((int)$booking->schedule->psikolog_id != (int)$psikolog->id) {
            abort(403, 'Unauthorized');
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Booking tidak dapat ditolak.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|min:10',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $request->cancel_reason,
        ]);

        // Make schedule available again
        $booking->schedule->update(['is_available' => true]);

        return back()->with('success', 'Booking berhasil ditolak.');
    }
}
