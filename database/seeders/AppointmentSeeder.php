<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctorIds = Doctor::query()->pluck('id');
        $patientIds = Patient::query()->pluck('id');

        if ($doctorIds->isEmpty() || $patientIds->isEmpty()) {
            return;
        }

        Appointment::factory()
            ->count(20)
            ->create([
                'doctor_id' => fake()->randomElement($doctorIds->all()),
                'patient_id' => fake()->randomElement($patientIds->all()),
            ]);
    }
}