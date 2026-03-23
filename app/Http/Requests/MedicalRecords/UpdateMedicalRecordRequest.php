<?php

namespace App\Http\Requests\MedicalRecords;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'blood_type' => ['nullable', 'string', 'max:3'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'chronic_diseases' => ['nullable', 'string', 'max:255'],
            'cronic_diseases' => ['nullable', 'string', 'max:255'],
            'medications' => ['nullable', 'string', 'max:255'],
            'family_history' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'blood_type' => 'tipo de sangre',
            'allergies' => 'alergias',
            'chronic_diseases' => 'enfermedades crónicas',
            'cronic_diseases' => 'enfermedades crónicas',
            'medications' => 'medicamentos',
            'family_history' => 'antecedentes familiares',
        ];
    }
}
