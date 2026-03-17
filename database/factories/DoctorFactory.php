<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->unique()->numberBetween(1, 20),
            'specialty' => fake()->randomElement(['Cardiology', 'Dermatology', 'Neurology', 'Pediatrics', 'Psychiatry']),
            'phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
