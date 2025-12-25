<?php

namespace App\Http\Controllers\Api;

use App\Models\Report;
use App\Models\User;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\Psikolog;
use App\Notifications\ReportSentNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReportController extends ApiController
{
    /**
     * Get reports list (admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::with('creator');

        // Filter by type
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($reports, 'Daftar laporan berhasil diambil.');
    }

    /**
     * Create a new report (admin).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'report_type' => 'required|in:monthly,quarterly,annual',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'summary' => 'nullable|string',
            ]);

            // Generate statistics for the period
            $statistics = $this->generateStatistics(
                $request->period_start,
                $request->period_end
            );

            $report = Report::create([
                'created_by' => Auth::id(),
                'title' => $request->title,
                'report_type' => $request->report_type,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'statistics' => $statistics,
                'summary' => $request->summary,
                'total_consultations' => $statistics['total_consultations'],
                'total_users' => $statistics['total_users'],
                'total_psikologs' => $statistics['total_psikologs'],
                'status' => 'draft',
            ]);

            return $this->success($report, 'Laporan berhasil dibuat.', 201);

        } catch (ValidationException $e) {
            return $this->error('Validasi gagal.', 422, $e->errors());
        }
    }

    /**
     * Get report detail.
     */
    public function show(Report $report): JsonResponse
    {
        $report->load('creator');

        return $this->success($report, 'Detail laporan berhasil diambil.');
    }

    /**
     * Send report to government (admin).
     */
    public function sendToGovernment(Report $report): JsonResponse
    {
        if ($report->status === 'sent') {
            return $this->error('Laporan sudah dikirim.', 400);
        }

        $report->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Notify all government users
        $governmentUsers = User::where('role', 'pemerintah')->get();
        foreach ($governmentUsers as $user) {
            try {
                $user->notify(new ReportSentNotification($report));
            } catch (\Exception $e) {
                \Log::error('Failed to send report notification: ' . $e->getMessage());
            }
        }

        return $this->success($report->fresh(), 'Laporan berhasil dikirim ke pemerintah.');
    }

    /**
     * Get reports for government users.
     */
    public function governmentReports(Request $request): JsonResponse
    {
        $query = Report::with('creator')
            ->where('status', 'sent');

        // Filter by type
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        $reports = $query->orderByDesc('sent_at')
            ->paginate($request->get('per_page', 10));

        return $this->paginated($reports, 'Daftar laporan berhasil diambil.');
    }

    /**
     * Generate statistics for a period.
     */
    private function generateStatistics(string $startDate, string $endDate): array
    {
        $totalConsultations = Consultation::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();

        $completedBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $cancelledBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cancelled')
            ->count();

        $totalUsers = User::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalPsikologs = Psikolog::where('verification_status', 'verified')
            ->whereBetween('verified_at', [$startDate, $endDate])
            ->count();

        $consultationsByType = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->selectRaw('schedules.consultation_type, COUNT(*) as count')
            ->groupBy('schedules.consultation_type')
            ->pluck('count', 'consultation_type')
            ->toArray();

        return [
            'total_consultations' => $totalConsultations,
            'total_bookings' => $totalBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'completion_rate' => $totalBookings > 0
                ? round(($completedBookings / $totalBookings) * 100, 2)
                : 0,
            'total_users' => $totalUsers,
            'total_psikologs' => $totalPsikologs,
            'consultations_by_type' => $consultationsByType,
        ];
    }
}
