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
            ],
            'phone' => ['nullable', 'string', 'max:9'],
            'birth_date' => ['nullable', 'date'],
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
