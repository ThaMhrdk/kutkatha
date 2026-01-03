<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'psikolog';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        // Load relasi psikolog jika belum ter-load
        $psikolog = $user->relationLoaded('psikolog')
            ? $user->psikolog
            : $user->psikolog()->first();

        return $psikolog && $psikolog->id === $schedule->psikolog_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role !== 'psikolog') {
            return false;
        }

        // Load relasi psikolog jika belum ter-load
        $psikolog = $user->relationLoaded('psikolog')
            ? $user->psikolog
            : $user->psikolog()->first();

        return $psikolog !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        // Load relasi psikolog jika belum ter-load
        $psikolog = $user->relationLoaded('psikolog')
            ? $user->psikolog
            : $user->psikolog()->first();

        return $psikolog && $psikolog->id === $schedule->psikolog_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        // Load relasi psikolog jika belum ter-load
        $psikolog = $user->relationLoaded('psikolog')
            ? $user->psikolog
            : $user->psikolog()->first();

        return $psikolog && $psikolog->id === $schedule->psikolog_id;
    }
}
