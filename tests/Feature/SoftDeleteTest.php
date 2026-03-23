<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('destroy endpoints perform soft deletes on appointments schedules and doctors', function () {
    Permission::findOrCreate('manage_appointments', 'web');
    Permission::findOrCreate('read_all_appointments', 'web');

    $assistant = User::factory()->create();
    $assistant->givePermissionTo(['manage_appointments', 'read_all_appointments']);
    Sanctum::actingAs($assistant);

    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $schedule = Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
    ]);

    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'status' => 'scheduled',
    ]);

    $this->deleteJson("/api/v1/appointments/{$appointment->id}")
        ->assertOk();
    $this->assertSoftDeleted('appointments', ['id' => $appointment->id]);

    $this->deleteJson("/api/v1/schedules/{$schedule->id}")
        ->assertOk();
    $this->assertSoftDeleted('schedules', ['id' => $schedule->id]);

    $this->deleteJson("/api/v1/doctors/{$doctor->id}")
        ->assertOk();
    $this->assertSoftDeleted('doctors', ['id' => $doctor->id]);
});
