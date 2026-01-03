<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat channel
Broadcast::channel('chat.{consultationId}', function ($user, $consultationId) {
    // Verify user has access to this consultation
    return \App\Models\Consultation::where('id', $consultationId)
        ->where(function ($query) use ($user) {
            $query->where('pengguna_id', $user->id)
                  ->orWhereHas('psikolog', function ($q) use ($user) {
                      $q->where('pengguna_id', $user->id);
                  });
        })->exists();
});

// Notification channel
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Booking updates channel
Broadcast::channel('booking.{bookingId}', function ($user, $bookingId) {
    return \App\Models\Booking::where('id', $bookingId)
        ->where('pengguna_id', $user->id)
        ->exists();
});
