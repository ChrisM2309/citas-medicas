<?php

namespace Database\Factories;

use App\Models\Appointment;
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
        $appointmentStartTime = fake()->numberBetween(8, 15);
        $appointmentEndTime = $appointmentStartTime + fake()->numberBetween(1, 2);

        return [
            'patient_id' => fake()->numberBetween(1, 20),
            'appointment_date' => fake()->date(),
            'appointment_start_time' => sprintf('%02d:00:00', $appointmentStartTime),
            'appointment_end_time' => sprintf('%02d:00:00', $appointmentEndTime),
            'status' => fake()->randomElement(['scheduled', 'completed', 'canceled']),
            'reason' => fake()->sentence(),
        ];
    }
}
