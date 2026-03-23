<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['ADMIN', 'ASSISTANT'];

        for ($i = 0; $i < 17; $i++) {
            $user = User::factory()->create();
            $rolAleatorio = fake()->randomElement($roles);
            $user->assignRole($rolAleatorio);
        }

        $admin = User::factory()->create([
            'email' => 'admin@correo.com',
            'password' => bcrypt('admin123'),
            'is_active' => true,
        ]);
        $admin->assignRole('ADMIN');

        $doctor = User::factory()->create([
            'email' => 'doctor@correo.com',
            'password' => bcrypt('doctor123'),
            'is_active' => true,
        ]);
        $doctor->assignRole('DOCTOR');

        $assistant = User::factory()->create([
            'email' => 'asistente@correo.com',
            'password' => bcrypt('asistente123'),
            'is_active' => true,
        ]);
        $assistant->assignRole('ASSISTANT');
    }
}
