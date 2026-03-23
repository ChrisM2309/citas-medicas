<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::query()->get()->each(function (Doctor $doctor): void {
            Schedule::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create([
                    'doctor_id' => $doctor->id,
                ]);
        });
    }
}
