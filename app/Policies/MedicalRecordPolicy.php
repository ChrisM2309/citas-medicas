<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalRecord;

class MedicalRecordPolicy
{
    /*
        PERMISOS IMPORTANTES
        - manage_medical_records: Permite crear, editar y ver registros médicos (para ADMIN y ASSISTANT)
    */ 

    public function view(User $user, MedicalRecord $record): bool
    {
        return $user->hasPermissionTo('manage_medical_records');
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $user->hasPermissionTo('manage_medical_records');
    }
}