<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        $doctorsIds = $doctors->pluck('id')->toArray();

        Appointment::factory(
            ['doctor_id' => fake()->randomElement($doctorsIds)]
        )->count(20)->create();
    }
}
