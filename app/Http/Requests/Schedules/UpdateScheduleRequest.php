<?php

namespace App\Http\Requests\Schedules;

class UpdateScheduleRequest extends BaseScheduleRequest
{
    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', ...$this->baseRules()['doctor_id']],
            'day_of_week' => ['sometimes', ...$this->baseRules()['day_of_week']],
            'start_time' => ['sometimes', ...$this->baseRules()['start_time']],
            'end_time' => ['sometimes', ...$this->baseRules()['end_time']],
        ];
    }
}
