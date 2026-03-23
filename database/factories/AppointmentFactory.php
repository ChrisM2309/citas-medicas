<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $durationHours = fake()->numberBetween(1, 2);

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'appointment_start_time' => sprintf('%02d:00:00', $startHour),
            'appointment_end_time' => sprintf('%02d:00:00', min($startHour + $durationHours, 23)),
            'status' => fake()->randomElement(['scheduled', 'completed', 'canceled']),
            'reason' => fake()->sentence(),
        ];
    }
}
