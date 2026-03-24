<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Appointments\Pages\Concerns\ValidatesAppointmentPayload;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    use ValidatesAppointmentPayload;

    protected static string $resource = AppointmentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->validateAndNormalizeAppointmentData($data);
    }
}
