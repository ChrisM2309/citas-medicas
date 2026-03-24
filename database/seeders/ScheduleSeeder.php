<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * @var array<int, array{day_of_week: string, start_time: string, end_time: string}>
     */
    private const BASE_SLOTS = [
        ['day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '12:00:00'],
        ['day_of_week' => 'Wednesday', 'start_time' => '13:00:00', 'end_time' => '17:00:00'],
        ['day_of_week' => 'Friday', 'start_time' => '09:00:00', 'end_time' => '13:00:00'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::query()->get()->each(function (Doctor $doctor): void {
            foreach (self::BASE_SLOTS as $slot) {
                Schedule::query()->firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        });
    }
}
