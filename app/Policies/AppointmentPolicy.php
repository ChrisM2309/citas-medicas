<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
{
    /*
        PERMISOS
        - read_all_appointments: Permite ver todas las citas (para ADMIN y ASSISTANT)
        - read_own_appointments: Permite ver sus citas asignadas (para DOCTOR)
        - manage_appointments: Permite crear, editar y eliminar citas (para ASSISTANT y ADMIN)
    */

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'read_all_appointments',
            'read_own_appointments',
            'manage_appointments',
        ]);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return true;
        }

        return $user->hasPermissionTo('read_own_appointments')
            && $user->doctor?->id === $appointment->doctor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('manage_appointments');
    }

    public function viewDoctorAppointments(User $user, int $doctorId): bool
    {
        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return true;
        }

        return $user->hasPermissionTo('read_own_appointments')
            && $user->doctor?->id === $doctorId;
    }

    public function viewPatientAppointments(User $user): bool
    {
        return $user->hasAnyPermission(['read_all_appointments', 'manage_appointments']);
    }
}
