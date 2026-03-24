<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : $this->input('email'),
            'phone' => $this->filled('phone') ? preg_replace('/\D+/', '', (string) $this->input('phone')) : $this->input('phone'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('patients', 'email')],
            'phone' => ['nullable', 'regex:/^\d{8,9}$/'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'max:1'],
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
