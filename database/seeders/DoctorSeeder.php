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
        // Ensure users with DOCTOR role have an associated doctor profile.
        User::query()
            ->role('DOCTOR')
            ->get()
            ->each(function (User $user): void {
                if ($user->doctor) {
                    return;
                }

                Doctor::factory()->create([
                    'user_id' => $user->id,
                ]);
            });

        $missingDoctors = max(0, 20 - Doctor::query()->count());

        if ($missingDoctors === 0) {
            return;
        }

        User::factory()
            ->count($missingDoctors)
            ->create()
            ->each(function (User $user): void {
                $user->assignRole('DOCTOR');

                Doctor::factory()->create([
                    'user_id' => $user->id,
                ]);
            });
    }
}
