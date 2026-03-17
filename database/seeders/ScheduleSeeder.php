<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {

            $diasDeTrabajo = fake()->randomElements(
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                3
            );

            foreach ($diasDeTrabajo as $dia) {
                Schedule::factory()->create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $dia,
                ]);
            }
        }
    }
}
