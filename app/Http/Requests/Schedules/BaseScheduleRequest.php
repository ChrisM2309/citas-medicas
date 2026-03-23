<?php

namespace App\Http\Requests\Schedules;

use App\Services\AppointmentValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class BaseScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'start_time.regex' => 'La hora de inicio debe estar en formato HH:MM o HH:MM:SS.',
            'end_time.regex' => 'La hora de fin debe estar en formato HH:MM o HH:MM:SS.',
        ];
    }

    public function attributes(): array
    {
        return [
            'doctor_id' => 'doctor',
            'day_of_week' => 'día de la semana',
            'start_time' => 'hora de inicio',
            'end_time' => 'hora de fin',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startInput = $this->input('start_time', $this->route('schedule')?->start_time);
            $endInput = $this->input('end_time', $this->route('schedule')?->end_time);
            $timeService = app(AppointmentValidationService::class);

            $start = $timeService->normalizeTime($startInput);
            $end = $timeService->normalizeTime($endInput);

            if (! $start || ! $end || $end <= $start) {
                $validator->errors()->add('end_time', 'La hora de fin debe ser posterior a la hora de inicio.');
            }
        });
    }

    protected function baseRules(): array
    {
        return [
            'doctor_id' => [
                'integer',
                Rule::exists('doctors', 'id')->whereNull('deleted_at'),
            ],
            'day_of_week' => [
                'string',
                Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
            ],
            'start_time' => ['regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ];
    }
}
