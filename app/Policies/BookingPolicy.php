<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // User owns the booking
        if ((int)$booking->user_id === (int)$user->id) {
            return true;
        }

        // Psikolog owns the schedule
        if ($user->psikolog && (int)$booking->schedule->psikolog_id === (int)$user->psikolog->id) {
            return true;
        }

        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // User can update their own pending booking
        if ((int)$booking->user_id === (int)$user->id && $booking->status === 'pending') {
            return true;
        }

        // Psikolog can update booking status
        if ($user->psikolog && (int)$booking->schedule->psikolog_id === (int)$user->psikolog->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // User can delete their own booking if pending or cancelled
        return (int)$booking->user_id === (int)$user->id
            && in_array($booking->status, ['pending', 'cancelled']);
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Only owner can cancel and only if not completed
        return (int)$booking->user_id === (int)$user->id && $booking->canBeCancelled();
    }

    /**
     * Determine whether the user can confirm the booking.
     */
    public function confirm(User $user, Booking $booking): bool
    {
        // Only psikolog of the schedule can confirm
        return $user->psikolog
            && (int)$booking->schedule->psikolog_id === (int)$user->psikolog->id
            && $booking->status === 'pending';
    }

    /**
     * Determine whether the user can manage the booking.
     */
    public function manage(User $user, Booking $booking): bool
    {
        // User owns the booking
        if ((int)$booking->user_id === (int)$user->id) {
            return true;
        }

        // Psikolog owns the schedule
        if ($user->psikolog && (int)$booking->schedule->psikolog_id === (int)$user->psikolog->id) {
            return true;
        }

        return false;
    }
}
