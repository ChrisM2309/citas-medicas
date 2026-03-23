<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (Patient::query()->doesntExist()) {
            $this->call(PatientSeeder::class);
        }

        if (Doctor::query()->doesntExist()) {
            $this->call(DoctorSeeder::class);
        }

        if (Schedule::query()->doesntExist()) {
            $this->call(ScheduleSeeder::class);
        }

        if (Appointment::query()->doesntExist()) {
            $this->call(AppointmentSeeder::class);
        }

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'is_active' => true,
            ]
        );
    }
}
