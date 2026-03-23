<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->count(20)
            ->create()
            ->each(function (User $user): void {
                Doctor::factory()->create([
                    'user_id' => $user->id,
                ]);
            });
    }
}
