<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy
{
    /**
     * Determine whether the user can view the consultation.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        // User owns the booking
        if ((int) $consultation->booking->user_id === (int) $user->id) {
            return true;
        }

        // Psikolog owns the schedule
        if ($user->psikolog && (int) $consultation->booking->schedule->psikolog_id === (int) $user->psikolog->id) {
            return true;
        }

        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the consultation.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        // Only psikolog can update consultation
        if ($user->psikolog && (int) $consultation->booking->schedule->psikolog_id === (int) $user->psikolog->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can manage the consultation.
     */
    public function manage(User $user, Consultation $consultation): bool
    {
        // Psikolog can manage their consultation
        if ($user->psikolog && (int) $consultation->booking->schedule->psikolog_id === (int) $user->psikolog->id) {
            return true;
        }

        // User can view and interact with their consultation
        if ((int) $consultation->booking->user_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can give feedback.
     */
    public function feedback(User $user, Consultation $consultation): bool
    {
        // Only the booking owner can give feedback
        return (int) $consultation->booking->user_id === (int) $user->id
            && $consultation->status === 'completed'
            && !$consultation->feedback;
    }
}
