<?php

namespace App\Http\Requests\Appointments;

use App\Models\Appointment;
use App\Services\AppointmentValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class BaseAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'appointment_start_time.regex' => 'La hora de inicio debe estar en formato HH:MM o HH:MM:SS.',
            'appointment_end_time.regex' => 'La hora de fin debe estar en formato HH:MM o HH:MM:SS.',
            'status.in' => 'El estado de la cita no es válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'paciente',
            'doctor_id' => 'doctor',
            'appointment_date' => 'fecha de cita',
            'appointment_start_time' => 'hora de inicio',
            'appointment_end_time' => 'hora de fin',
            'reason' => 'motivo',
            'status' => 'estado',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $payload = $this->resolvedPayload();
            $service = app(AppointmentValidationService::class);

            if (
                ! isset(
                    $payload['doctor_id'],
                    $payload['appointment_date'],
                    $payload['appointment_start_time'],
                    $payload['appointment_end_time'],
                )
            ) {
                return;
            }

            $start = $service->normalizeTime($payload['appointment_start_time']);
            $end = $service->normalizeTime($payload['appointment_end_time']);

            if (! $start || ! $end || $end <= $start) {
                $validator->errors()->add('appointment_end_time', 'La hora de fin debe ser posterior a la hora de inicio.');

                return;
            }

            $payload['appointment_start_time'] = $start;
            $payload['appointment_end_time'] = $end;

            if (($payload['status'] ?? 'scheduled') === 'canceled') {
                return;
            }

            if (! $service->isWithinDoctorSchedule($payload)) {
                $validator->errors()->add(
                    'appointment_start_time',
                    'El doctor no tiene disponibilidad programada para este día u horario.',
                );

                return;
            }

            if ($service->hasConflict($payload, $this->currentAppointment())) {
                $validator->errors()->add(
                    'appointment_start_time',
                    'El doctor ya tiene una cita asignada en este rango.',
                );
            }
        });
    }

    /**
     * @return array{
     *     patient_id?: int|string,
     *     doctor_id?: int|string,
     *     appointment_date?: string,
     *     appointment_start_time?: string,
     *     appointment_end_time?: string,
     *     reason?: string|null,
     *     status?: string|null
     * }
     */
    protected function resolvedPayload(): array
    {
        $appointment = $this->currentAppointment();

        return [
            'patient_id' => $this->input('patient_id', $appointment?->patient_id),
            'doctor_id' => $this->input('doctor_id', $appointment?->doctor_id),
            'appointment_date' => $this->input('appointment_date', $appointment?->appointment_date),
            'appointment_start_time' => $this->input('appointment_start_time', $appointment?->appointment_start_time),
            'appointment_end_time' => $this->input('appointment_end_time', $appointment?->appointment_end_time),
            'reason' => $this->input('reason', $appointment?->reason),
            'status' => $this->input('status', $appointment?->status ?? 'scheduled'),
        ];
    }

    protected function currentAppointment(): ?Appointment
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment ? $appointment : null;
    }

    protected function baseRules(): array
    {
        return [
            'patient_id' => [
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at'),
            ],
            'doctor_id' => [
                'integer',
                Rule::exists('doctors', 'id')->whereNull('deleted_at'),
            ],
            'appointment_date' => ['date'],
            'appointment_start_time' => ['regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'appointment_end_time' => ['regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:20', Rule::in(['scheduled', 'completed', 'canceled'])],
        ];
    }
}
