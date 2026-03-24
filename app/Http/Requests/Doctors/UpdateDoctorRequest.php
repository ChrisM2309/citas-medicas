<?php

namespace App\Http\Requests\Doctors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('phone')) {
            return;
        }

        $this->merge([
            'phone' => $this->filled('phone')
                ? preg_replace('/\D+/', '', (string) $this->input('phone'))
                : $this->input('phone'),
        ]);
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor')?->id;

        return [
            'user_id' => [
                'sometimes',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('doctors', 'user_id')->ignore($doctorId),
            ],
            'specialty' => ['sometimes', 'string', 'max:50'],
            'phone' => ['nullable', 'regex:/^\d{8,9}$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'usuario',
            'specialty' => 'especialidad',
            'phone' => 'teléfono',
        ];
    }
}
