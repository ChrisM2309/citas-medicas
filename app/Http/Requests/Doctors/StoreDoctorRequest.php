<?php

namespace App\Http\Requests\Doctors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('doctors', 'user_id'),
            ],
            'specialty' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:9'],
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
