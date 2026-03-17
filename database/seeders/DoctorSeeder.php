<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 19; $i++) {
            $user = User::factory()->create();
            $user->assignRole('DOCTOR');
        }

        $users = User::role('DOCTOR')->get();

        foreach ($users as $user) {
            Doctor::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
