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
        return [
            'patient_id' => fake()->numberBetween(1, 20),
            'doctor_id' => fake()->numberBetween(1, 20),
            'appointment_date' => fake()->date(),
            'appointment_start_time' => fake()->time('H:i:s'),
            'appointment_end_time' => fake()->time('H:i:s'),
            'status' => fake()->randomElement(['scheduled', 'completed', 'canceled']),
            'reason' => fake()->sentence(),
        ];
    }
}
