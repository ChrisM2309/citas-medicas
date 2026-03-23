<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $doctor = Role::firstOrCreate(['name' => 'DOCTOR', 'guard_name' => 'web']);
        $assistant = Role::firstOrCreate(['name' => 'ASSISTANT', 'guard_name' => 'web']);

        // Permiso para leer pacientes → todos
        Permission::firstOrCreate(['name' => 'read_patients', 'guard_name'=> 'web']);
        $doctor->givePermissionTo('read_patients');

        
        // Permisos de medico
        collect([
            // Puede leer su propia agenda
            'read_appointments',
            // Puede administrar los expedientes de sus pacientes
            'manage_medical_records',
        ])->each(function (string $permission) use ($doctor): void {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $doctor->givePermissionTo($permission);
        });

        // Permisos de asistente
        collect([
            // Puede ver la agenda de todos los medicos
            'read_all_appointments',
            // Puede administrar pacientes
            'manage_patients',
            // Puede administrar citas
            'manage_appointments',
        ])->each(function (string $permission) use ($assistant): void {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $assistant->givePermissionTo($permission);
        });

        // Permiso para administrar usuarios (solo para admin)
        Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());
    }
}
