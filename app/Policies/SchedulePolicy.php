<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['read_appointments', 'read_all_appointments', 'manage_appointments']);
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return true;
        }

        return $user->hasPermissionTo('read_appointments') && $user->doctor?->id === $schedule->doctor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function viewDoctorSchedules(User $user, int $doctorId): bool
    {
        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return true;
        }

        return $user->hasPermissionTo('read_appointments') && $user->doctor?->id === $doctorId;
    }
}
