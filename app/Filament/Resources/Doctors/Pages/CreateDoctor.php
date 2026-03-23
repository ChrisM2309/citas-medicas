<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Models\Doctor;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return Doctor::create([
            'user_id' => $user->id,
            'phone' => $data['phone'] ?? null,
            'specialty' => $data['specialty'],
        ]);
    }
}
