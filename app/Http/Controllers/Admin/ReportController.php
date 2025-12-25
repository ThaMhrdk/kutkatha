<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Psikolog;
use App\Models\Consultation;
use App\Models\Booking;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Statistics for cards
        $totalConsultations = Consultation::where('status', 'completed')->count();
        // Revenue calculation - schedule consultation_fee * completed bookings
        $totalRevenue = Booking::join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('psikologs', 'schedules.psikolog_id', '=', 'psikologs.id')
            ->where('bookings.status', 'confirmed')
            ->sum('psikologs.consultation_fee');
        $totalUsers = User::where('role', 'user')->count();
        $totalPsikologs = Psikolog::where('verification_status', 'verified')->count();

        // Additional stats
        $averageRating = round(Feedback::avg('rating') ?? 0, 1);
        $activeUsers = User::where('role', 'user')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        return view('admin.report.index', compact(
            'reports',
            'totalConsultations',
            'totalRevenue',
            'totalUsers',
            'totalPsikologs',
            'averageRating',
            'activeUsers'
        ));
    }

    public function create()
    {
        return view('admin.report.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'report_type' => 'required|in:monthly,quarterly,annual',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'summary' => 'nullable|string',
        ]);

        $totalConsultations = Consultation::whereBetween('created_at', [$request->period_start, $request->period_end])
            ->where('status', 'completed')
            ->count();

        $totalUsers = User::where('role', 'user')
            ->whereBetween('created_at', [$request->period_start, $request->period_end])
            ->count();

        $totalPsikologs = Psikolog::whereBetween('created_at', [$request->period_start, $request->period_end])
            ->count();

        $statistics = [
            'consultations_by_type' => Booking::selectRaw('consultation_type, COUNT(*) as count')
                ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
                ->whereBetween('bookings.created_at', [$request->period_start, $request->period_end])
                ->groupBy('consultation_type')
                ->pluck('count', 'consultation_type')
                ->toArray(),
            'average_rating' => Consultation::with('feedback')
                ->whereBetween('consultations.created_at', [$request->period_start, $request->period_end])
                ->get()
                ->pluck('feedback.rating')
                ->filter()
                ->avg() ?? 0,
        ];

        Report::create([
            'created_by' => Auth::id(),
            'title' => $request->title,
            'report_type' => $request->report_type,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'total_consultations' => $totalConsultations,
            'total_users' => $totalUsers,
            'total_psikologs' => $totalPsikologs,
            'statistics' => $statistics,
            'summary' => $request->summary,
        ]);

        return redirect()->route('admin.report.index')
            ->with('success', 'Laporan berhasil dibuat.');
    }

    public function show(Report $report)
    {
        return view('admin.report.show', compact('report'));
    }

    public function send(Report $report)
    {
        $report->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // TODO: Send to pemerintah via email/notification

        return back()->with('success', 'Laporan berhasil dikirim ke Pemerintah.');
    }
}
