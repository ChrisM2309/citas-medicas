<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Doctor;

class DoctorPolicy
{

    // PERMISOS IMPORTANTES
    // manage_users: para crear, editar y eliminar doctores (solo admin)

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['manage_users', 'manage_patients', 'read_all_appointments']);
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->hasAnyPermission(['manage_users', 'manage_patients', 'read_own_appointments']) || $user->id === $doctor->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_users');
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo('manage_users') || $user->id === $doctor->user_id;
    }

    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->hasPermissionTo('manage_users');
    }
}
