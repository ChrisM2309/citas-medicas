<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->user->name;
        $data['email'] = $this->record->user->email;
        $data['is_active'] = $this->record->user->is_active;
        $data['password'] = '';
        $data['phone'] = $this->record->phone;
        $data['specialty'] = $this->record->specialty;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if (filled($data['password'] ?? null)) {
            $userData['password'] = Hash::make($data['password']);
        }

        $record->user->update($userData);

        $record->update([
            'phone' => $data['phone'] ?? null,
            'specialty' => $data['specialty'],
        ]);

        return $record;
    }
}
