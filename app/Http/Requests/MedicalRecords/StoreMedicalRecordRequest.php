<?php

namespace App\Http\Requests\MedicalRecords;

use App\Models\MedicalRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $chronicDiseases = $this->input('chronic_diseases');
        $cronicDiseases = $this->input('cronic_diseases');

        $this->merge([
            'blood_type' => $this->filled('blood_type') ? strtoupper(trim((string) $this->input('blood_type'))) : $this->input('blood_type'),
            'chronic_diseases' => $chronicDiseases ?? $cronicDiseases,
        ]);
    }

    public function rules(): array
    {
        return [
            'blood_type' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'allergies' => ['nullable', 'string', 'max:255'],
            'chronic_diseases' => ['nullable', 'string', 'max:255'],
            'cronic_diseases' => ['nullable', 'string', 'max:255'],
            'medications' => ['nullable', 'string', 'max:255'],
            'family_history' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $patientId = $this->route('patient')?->id;

            if (
                $this->filled('chronic_diseases')
                && $this->filled('cronic_diseases')
                && $this->input('chronic_diseases') !== $this->input('cronic_diseases')
            ) {
                $validator->errors()->add('chronic_diseases', 'Debes enviar solo un campo coherente para enfermedades cronicas.');
            }

            if ($patientId && MedicalRecord::query()->where('patient_id', $patientId)->exists()) {
                $validator->errors()->add('patient_id', 'El paciente ya tiene un expediente medico registrado.');
            }
        });
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
