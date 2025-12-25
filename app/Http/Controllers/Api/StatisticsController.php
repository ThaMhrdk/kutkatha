<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\Psikolog;
use App\Models\Article;
use App\Models\ForumTopic;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends ApiController
{
    /**
     * Get admin statistics.
     */
    public function admin(): JsonResponse
    {
        $stats = [
            'users' => [
                'total' => User::where('role', 'user')->count(),
                'active' => User::where('role', 'user')->where('is_active', true)->count(),
                'new_this_month' => User::where('role', 'user')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'psikologs' => [
                'total' => Psikolog::count(),
                'verified' => Psikolog::where('verification_status', 'verified')->count(),
                'pending' => Psikolog::where('verification_status', 'pending')->count(),
                'rejected' => Psikolog::where('verification_status', 'rejected')->count(),
            ],
            'bookings' => [
                'total' => Booking::count(),
                'pending' => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'completed' => Booking::where('status', 'completed')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count(),
                'this_month' => Booking::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'consultations' => [
                'total' => Consultation::count(),
                'completed' => Consultation::where('status', 'completed')->count(),
                'in_progress' => Consultation::where('status', 'in_progress')->count(),
            ],
            'revenue' => [
                'total' => Payment::where('status', 'paid')->sum('amount'),
                'this_month' => Payment::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
            ],
            'forum' => [
                'topics' => ForumTopic::count(),
                'active_topics' => ForumTopic::where('is_closed', false)->count(),
            ],
            'articles' => [
                'total' => Article::count(),
                'published' => Article::where('status', 'published')->count(),
            ],
        ];

        return $this->success($stats, 'Statistik admin berhasil diambil.');
    }

    /**
     * Get government statistics.
     */
    public function government(): JsonResponse
    {
        $stats = [
            'overview' => [
                'total_users' => User::where('role', 'user')->count(),
                'total_psikologs' => Psikolog::where('verification_status', 'verified')->count(),
                'total_consultations' => Consultation::where('status', 'completed')->count(),
                'total_articles' => Article::where('status', 'published')->count(),
            ],
            'consultation_types' => DB::table('schedules')
                ->join('bookings', 'schedules.id', '=', 'bookings.schedule_id')
                ->where('bookings.status', 'completed')
                ->select('schedules.consultation_type', DB::raw('COUNT(*) as count'))
                ->groupBy('schedules.consultation_type')
                ->get(),
            'monthly_consultations' => $this->getMonthlyConsultations(),
            'top_specializations' => Psikolog::where('verification_status', 'verified')
                ->select('specialization', DB::raw('COUNT(*) as count'))
                ->groupBy('specialization')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
        ];

        return $this->success($stats, 'Statistik pemerintah berhasil diambil.');
    }

    /**
     * Get monthly statistics.
     */
    public function monthly(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $stats = [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
            ],
            'users' => [
                'new' => User::where('role', 'user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'total_active' => User::where('role', 'user')
                    ->where('is_active', true)
                    ->count(),
            ],
            'consultations' => [
                'total' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
                'completed' => Booking::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->count(),
                'by_type' => DB::table('schedules')
                    ->join('bookings', 'schedules.id', '=', 'bookings.schedule_id')
                    ->whereBetween('bookings.created_at', [$startDate, $endDate])
                    ->where('bookings.status', 'completed')
                    ->select('schedules.consultation_type', DB::raw('COUNT(*) as count'))
                    ->groupBy('schedules.consultation_type')
                    ->get(),
            ],
            'revenue' => Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('amount'),
            'daily_breakdown' => $this->getDailyBreakdown($startDate, $endDate),
        ];

        return $this->success($stats, 'Statistik bulanan berhasil diambil.');
    }

    /**
     * Get yearly statistics.
     */
    public function yearly(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $startDate = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();

        $stats = [
            'year' => $year,
            'totals' => [
                'users' => User::where('role', 'user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'psikologs' => Psikolog::where('verification_status', 'verified')
                    ->whereBetween('verified_at', [$startDate, $endDate])
                    ->count(),
                'consultations' => Booking::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Payment::where('status', 'paid')
                    ->whereBetween('paid_at', [$startDate, $endDate])
                    ->sum('amount'),
            ],
            'monthly_breakdown' => $this->getYearlyMonthlyBreakdown($year),
            'growth' => $this->calculateGrowth($year),
        ];

        return $this->success($stats, 'Statistik tahunan berhasil diambil.');
    }

    /**
     * Get monthly consultations for the last 12 months.
     */
    private function getMonthlyConsultations(): array
    {
        $result = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Booking::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'completed')
                ->count();

            $result[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * Get daily breakdown for a month.
     */
    private function getDailyBreakdown($startDate, $endDate): array
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Get monthly breakdown for a year.
     */
    private function getYearlyMonthlyBreakdown(int $year): array
    {
        $result = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $result[] = [
                'month' => $startDate->format('M'),
                'consultations' => Booking::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->count(),
                'revenue' => Payment::where('status', 'paid')
                    ->whereBetween('paid_at', [$startDate, $endDate])
                    ->sum('amount'),
                'new_users' => User::where('role', 'user')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ];
        }

        return $result;
    }

    /**
     * Calculate year-over-year growth.
     */
    private function calculateGrowth(int $year): array
    {
        $currentYearStart = \Carbon\Carbon::create($year, 1, 1);
        $currentYearEnd = \Carbon\Carbon::create($year, 12, 31);
        $lastYearStart = \Carbon\Carbon::create($year - 1, 1, 1);
        $lastYearEnd = \Carbon\Carbon::create($year - 1, 12, 31);

        $currentConsultations = Booking::whereBetween('created_at', [$currentYearStart, $currentYearEnd])
            ->where('status', 'completed')
            ->count();

        $lastConsultations = Booking::whereBetween('created_at', [$lastYearStart, $lastYearEnd])
            ->where('status', 'completed')
            ->count();

        $consultationGrowth = $lastConsultations > 0
            ? round((($currentConsultations - $lastConsultations) / $lastConsultations) * 100, 2)
            : 0;

        $currentRevenue = Payment::where('status', 'paid')
            ->whereBetween('paid_at', [$currentYearStart, $currentYearEnd])
            ->sum('amount');

        $lastRevenue = Payment::where('status', 'paid')
            ->whereBetween('paid_at', [$lastYearStart, $lastYearEnd])
            ->sum('amount');

        $revenueGrowth = $lastRevenue > 0
            ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 2)
            : 0;

        return [
            'consultations' => [
                'current' => $currentConsultations,
                'previous' => $lastConsultations,
                'growth_percentage' => $consultationGrowth,
            ],
            'revenue' => [
                'current' => $currentRevenue,
                'previous' => $lastRevenue,
                'growth_percentage' => $revenueGrowth,
            ],
        ];
    }
}
