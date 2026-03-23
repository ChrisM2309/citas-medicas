<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Schedule;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['read_appointments', 'read_all_appointments', 'manage_appointments']);
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasPermissionTo('read_all_appointments')) return true;
        return $user->hasPermissionTo('read_appointments') && $user->doctor?->id === $schedule->doctor_id;
    }

    public function update(User $user, Schedule $schedule): bool
    {
        // Solo quien gestiona citas (Asistente según tu seeder)
        return $user->hasPermissionTo('manage_appointments');
    }
}
