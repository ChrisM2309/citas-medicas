<?php

namespace App\Http\Requests\Appointments;

class UpdateAppointmentRequest extends BaseAppointmentRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => ['sometimes', ...$this->baseRules()['patient_id']],
            'doctor_id' => ['sometimes', ...$this->baseRules()['doctor_id']],
            'appointment_date' => ['sometimes', ...$this->baseRules()['appointment_date']],
            'appointment_start_time' => ['sometimes', ...$this->baseRules()['appointment_start_time']],
            'appointment_end_time' => ['sometimes', ...$this->baseRules()['appointment_end_time']],
            'reason' => $this->baseRules()['reason'],
            'status' => $this->baseRules()['status'],
        ];
    }
}
