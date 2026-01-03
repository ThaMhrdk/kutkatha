<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PsikologController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\CampaignController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful API endpoints for Kutkatha Application
| All API routes are prefixed with /api
|
*/

// Authentication
Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // Public articles
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);

    // Public psikolog list (for browsing)
    Route::get('/psikologs', [PsikologController::class, 'index']);
    Route::get('/psikologs/{psikolog}', [PsikologController::class, 'show']);
    Route::get('/psikologs/{psikolog}/schedules', [ScheduleController::class, 'getByPsikolog']);

    // Public campaigns
    Route::get('/campaigns', [CampaignController::class, 'index']);
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show']);

    // Protected API Routes
    Route::middleware('auth:sanctum')->group(function () {

        // User Profile
        Route::get('/user', function (Request $request) {
            return response()->json([
                'success' => true,
                'data' => $request->user()->load('psikolog')
            ]);
        });
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | User Endpoints
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:user')->prefix('user')->group(function () {
            // Bookings
            Route::apiResource('bookings', BookingController::class);
            Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
            Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule']);
            Route::post('/bookings/{booking}/payment', [BookingController::class, 'processPayment']);

            // Consultations
            Route::get('/consultations', [ConsultationController::class, 'index']);
            Route::get('/consultations/{consultation}', [ConsultationController::class, 'show']);
            Route::post('/consultations/{consultation}/feedback', [ConsultationController::class, 'storeFeedback']);

            // Chat by Consultation
            Route::get('/consultations/{consultation}/messages', [ChatController::class, 'getMessages']);
            Route::post('/consultations/{consultation}/messages', [ChatController::class, 'sendMessage']);

            // Chat by Booking (auto-create consultation)
            Route::get('/bookings/{booking}/chat', [ChatController::class, 'getMessagesByBooking']);
            Route::post('/bookings/{booking}/chat', [ChatController::class, 'sendMessageByBooking']);
        });

        /*
        |--------------------------------------------------------------------------
        | Psikolog Endpoints
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:psikolog')->prefix('psikolog')->group(function () {
            // Schedules
            Route::apiResource('schedules', ScheduleController::class);

            // Booking Management
            Route::get('/bookings', [BookingController::class, 'psikologBookings']);
            Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
            Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);

            // Consultations
            Route::get('/consultations', [ConsultationController::class, 'psikologConsultations']);
            Route::post('/consultations/{booking}/start', [ConsultationController::class, 'start']);
            Route::post('/consultations/{consultation}/complete', [ConsultationController::class, 'complete']);
            Route::put('/consultations/{consultation}', [ConsultationController::class, 'update']);

            // Chat by Consultation
            Route::get('/consultations/{consultation}/messages', [ChatController::class, 'getMessages']);
            Route::post('/consultations/{consultation}/messages', [ChatController::class, 'sendMessage']);

            // Chat by Booking (auto-create consultation)
            Route::get('/bookings/{booking}/chat', [ChatController::class, 'getMessagesByBooking']);
            Route::post('/bookings/{booking}/chat', [ChatController::class, 'sendMessageByBooking']);

            // Articles
            Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
        });

        /*
        |--------------------------------------------------------------------------
        | Admin Endpoints
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Psikolog Verification
            Route::get('/psikologs/pending', [PsikologController::class, 'pending']);
            Route::post('/psikologs/{psikolog}/verify', [PsikologController::class, 'verify']);
            Route::post('/psikologs/{psikolog}/reject', [PsikologController::class, 'reject']);

            // Reports
            Route::apiResource('reports', ReportController::class);
            Route::post('/reports/{report}/send', [ReportController::class, 'sendToGovernment']);

            // Forum Management
            Route::get('/forum/topics', [ForumController::class, 'adminTopics']);
            Route::delete('/forum/posts/{post}', [ForumController::class, 'deletePost']);
            Route::delete('/forum/comments/{comment}', [ForumController::class, 'deleteComment']);

            // Statistics
            Route::get('/statistics', [StatisticsController::class, 'admin']);
        });

        /*
        |--------------------------------------------------------------------------
        | Pemerintah (Government) Endpoints
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:pemerintah')->prefix('government')->group(function () {
            // Reports
            Route::get('/reports', [ReportController::class, 'governmentReports']);
            Route::get('/reports/{report}', [ReportController::class, 'show']);

            // Statistics
            Route::get('/statistics', [StatisticsController::class, 'government']);
            Route::get('/statistics/monthly', [StatisticsController::class, 'monthly']);
            Route::get('/statistics/yearly', [StatisticsController::class, 'yearly']);

            // Campaigns (Mengelola Kampanye Edukasi)
            Route::get('/campaigns', [CampaignController::class, 'adminIndex']);
            Route::post('/campaigns', [CampaignController::class, 'store']);
            Route::put('/campaigns/{campaign}', [CampaignController::class, 'update']);
            Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy']);
            Route::post('/campaigns/{campaign}/publish', [CampaignController::class, 'publish']);
        });

        /*
        |--------------------------------------------------------------------------
        | Forum Endpoints (All Authenticated Users)
        |--------------------------------------------------------------------------
        */
        Route::prefix('forum')->group(function () {
            Route::get('/topics', [ForumController::class, 'topics']);
            Route::post('/topics', [ForumController::class, 'storeTopic']);
            Route::get('/topics/{topic}', [ForumController::class, 'showTopic']);
            Route::get('/topics/{topic}/posts', [ForumController::class, 'posts']);
            Route::post('/topics/{topic}/posts', [ForumController::class, 'storePost']);
            Route::get('/posts/{post}/comments', [ForumController::class, 'comments']);
            Route::post('/posts/{post}/comments', [ForumController::class, 'storeComment']);
        });
    });
