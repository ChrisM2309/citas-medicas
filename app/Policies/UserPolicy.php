<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /*
        PERMISOS IMPORTANTES
        - manage_users: Permite crear, editar, eliminar y ver usuarios (para ADMIN)
    */
    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('manage_users');
    }

    public function create(User $user): bool {
        return $user->hasPermissionTo('manage_users');
    }

    public function update(User $user, User $model): bool {
        return $user->hasPermissionTo('manage_users');
    }

    public function delete(User $user, User $model): bool {
        return $user->hasPermissionTo('manage_users');
    }
}