<?php

namespace App\Http\Requests\Schedules;

class StoreScheduleRequest extends BaseScheduleRequest
{
    public function rules(): array
    {
        return [
            'doctor_id' => ['required', ...$this->baseRules()['doctor_id']],
            'day_of_week' => ['required', ...$this->baseRules()['day_of_week']],
            'start_time' => ['required', ...$this->baseRules()['start_time']],
            'end_time' => ['required', ...$this->baseRules()['end_time']],
        ];
    }
}
