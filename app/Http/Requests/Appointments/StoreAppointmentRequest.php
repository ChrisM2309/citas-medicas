<?php

namespace App\Http\Requests\Appointments;

class StoreAppointmentRequest extends BaseAppointmentRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => ['required', ...$this->baseRules()['patient_id']],
            'doctor_id' => ['required', ...$this->baseRules()['doctor_id']],
            'appointment_date' => ['required', ...$this->baseRules()['appointment_date']],
            'appointment_start_time' => ['required', ...$this->baseRules()['appointment_start_time']],
            'appointment_end_time' => ['required', ...$this->baseRules()['appointment_end_time']],
            'reason' => $this->baseRules()['reason'],
            'status' => $this->baseRules()['status'],
        ];
    }
}
