<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Consultation;
use App\Models\Schedule;
use App\Models\Article;
use App\Policies\BookingPolicy;
use App\Policies\ConsultationPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ArticlePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Consultation::class, ConsultationPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
    }
}

