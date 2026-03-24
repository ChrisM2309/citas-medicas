<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->exists('email')) {
            $normalized['email'] = $this->filled('email')
                ? mb_strtolower(trim((string) $this->input('email')))
                : $this->input('email');
        }

        if ($this->exists('phone')) {
            $normalized['phone'] = $this->filled('phone')
                ? preg_replace('/\D+/', '', (string) $this->input('phone'))
                : $this->input('phone');
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'lastname' => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'email',
                'max:100',
                Rule::unique('patients', 'email')->ignore($patientId),
            ],
            'phone' => ['nullable', 'regex:/^\d{8,9}$/'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['sometimes', 'string', 'max:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'lastname' => 'apellido',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'birth_date' => 'fecha de nacimiento',
            'gender' => 'género',
        ];
    }
}
