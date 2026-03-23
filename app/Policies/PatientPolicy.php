<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;

class PatientPolicy
{
    /*
        PERMISOS IMPORTANTES
        - manage_patients: Permite crear, editar, eliminar y ver pacientes (para ADMIN y ASSISTANT)
    */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('manage_patients');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_patients');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('manage_patients');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasPermissionTo('manage_patients');
    }
}
