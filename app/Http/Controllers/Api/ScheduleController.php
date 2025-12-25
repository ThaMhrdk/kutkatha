<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use App\Models\Psikolog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ScheduleController extends ApiController
{
    /**
     * Get schedules for authenticated psikolog.
     */
    public function index(Request $request): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if (!$psikolog) {
            return $this->error('Profil psikolog tidak ditemukan.', 404);
        }

        $query = $psikolog->schedules()->with('bookings');

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        // Filter by type
        if ($request->filled('consultation_type')) {
            $query->where('consultation_type', $request->consultation_type);
        }

        // Filter by availability
        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $schedules = $query->orderBy('date')
            ->orderBy('start_time')
            ->paginate($request->get('per_page', 15));

        return $this->paginated($schedules, 'Jadwal berhasil diambil.');
    }

    /**
     * Get available schedules for a specific psikolog.
     */
    public function getByPsikolog(Request $request, Psikolog $psikolog): JsonResponse
    {
        $query = $psikolog->schedules()
            ->where('date', '>=', now()->toDateString())
            ->where('is_available', true);

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        // Filter by consultation type
        if ($request->filled('consultation_type')) {
            $query->where('consultation_type', $request->consultation_type);
        }

        $schedules = $query->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->filter(function ($schedule) {
                return !$schedule->isBooked();
            })
            ->values();

        return $this->success($schedules, 'Jadwal tersedia berhasil diambil.');
    }

    /**
     * Create a new schedule.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if (!$psikolog) {
                return $this->error('Profil psikolog tidak ditemukan.', 404);
            }

            if (!$psikolog->isVerified()) {
                return $this->error('Akun Anda belum diverifikasi.', 403);
            }

            $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'consultation_type' => 'required|in:online,offline,chat',
                'location' => 'required_if:consultation_type,offline|nullable|string',
            ]);

            // Check for overlapping schedules
            $overlapping = $psikolog->schedules()
                ->where('date', $request->date)
                ->where(function ($q) use ($request) {
                    $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q2) use ($request) {
                          $q2->where('start_time', '<=', $request->start_time)
                             ->where('end_time', '>=', $request->end_time);
                      });
                })
                ->exists();

            if ($overlapping) {
                return $this->error('Jadwal bertabrakan dengan jadwal yang sudah ada.', 422);
            }

            $schedule = Schedule::create([
                'psikolog_id' => $psikolog->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'consultation_type' => $request->consultation_type,
                'location' => $request->location,
                'is_available' => true,
            ]);

            return $this->success($schedule, 'Jadwal berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get schedule detail.
     */
    public function show(Schedule $schedule): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if ($schedule->psikolog_id !== $psikolog->id) {
            return $this->error('Unauthorized.', 403);
        }

        $schedule->load(['bookings.user', 'bookings.payment']);

        return $this->success($schedule, 'Detail jadwal berhasil diambil.');
    }

    /**
     * Update schedule.
     */
    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        try {
            $psikolog = Auth::user()->psikolog;

            if ($schedule->psikolog_id !== $psikolog->id) {
                return $this->error('Unauthorized.', 403);
            }

            // Can't update if already booked
            if ($schedule->isBooked()) {
                return $this->error('Jadwal sudah di-booking dan tidak bisa diubah.', 400);
            }

            $request->validate([
                'date' => 'sometimes|date|after_or_equal:today',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'consultation_type' => 'sometimes|in:online,offline,chat',
                'location' => 'nullable|string',
                'is_available' => 'sometimes|boolean',
            ]);

            $schedule->update($request->only([
                'date', 'start_time', 'end_time',
                'consultation_type', 'location', 'is_available'
            ]));

            return $this->success($schedule->fresh(), 'Jadwal berhasil diperbarui.');

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Delete schedule.
     */
    public function destroy(Schedule $schedule): JsonResponse
    {
        $psikolog = Auth::user()->psikolog;

        if ($schedule->psikolog_id !== $psikolog->id) {
            return $this->error('Unauthorized.', 403);
        }

        if ($schedule->isBooked()) {
            return $this->error('Jadwal sudah di-booking dan tidak bisa dihapus.', 400);
        }

        $schedule->delete();

        return $this->success(null, 'Jadwal berhasil dihapus.');
    }
}
