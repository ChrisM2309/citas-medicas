<?php

namespace App\Filament\Resources\Appointments\Pages\Concerns;

use App\Models\Appointment;
use App\Services\AppointmentValidationService;
use Illuminate\Validation\ValidationException;

trait ValidatesAppointmentPayload
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function validateAndNormalizeAppointmentData(array $data, ?Appointment $ignore = null): array
    {
        $service = app(AppointmentValidationService::class);

        $data = [
            ...$data,
            'doctor_id' => $data['doctor_id'] ?? $ignore?->doctor_id,
            'appointment_date' => $data['appointment_date'] ?? $ignore?->appointment_date,
            'appointment_start_time' => $data['appointment_start_time'] ?? $ignore?->appointment_start_time,
            'appointment_end_time' => $data['appointment_end_time'] ?? $ignore?->appointment_end_time,
            'status' => $data['status'] ?? $ignore?->status ?? 'scheduled',
        ];

        $start = $service->normalizeTime((string) ($data['appointment_start_time'] ?? ''));
        $end = $service->normalizeTime((string) ($data['appointment_end_time'] ?? ''));

        if (! $start || ! $end || $end <= $start) {
            throw ValidationException::withMessages([
                'data.appointment_end_time' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);
        }

        $durationInMinutes = (strtotime($end) - strtotime($start)) / 60;

        if ($durationInMinutes < 15 || $durationInMinutes > 240) {
            throw ValidationException::withMessages([
                'data.appointment_end_time' => 'La cita debe durar entre 15 minutos y 4 horas.',
            ]);
        }

        $data['appointment_start_time'] = $start;
        $data['appointment_end_time'] = $end;

        if (($data['status'] ?? 'scheduled') === 'canceled') {
            return $data;
        }

        $payload = [
            'doctor_id' => $data['doctor_id'] ?? null,
            'appointment_date' => (string) ($data['appointment_date'] ?? ''),
            'appointment_start_time' => $start,
            'appointment_end_time' => $end,
        ];

        if (! $service->isWithinDoctorSchedule($payload)) {
            throw ValidationException::withMessages([
                'data.appointment_start_time' => 'El doctor no tiene disponibilidad programada para este dia u horario.',
            ]);
        }

        if ($service->hasConflict($payload, $ignore)) {
            throw ValidationException::withMessages([
                'data.appointment_start_time' => 'El doctor ya tiene una cita asignada en este rango.',
            ]);
        }

        return $data;
    }
}
