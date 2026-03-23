<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function grantPermissions(User $user, array $permissions): void
{
    foreach ($permissions as $permissionName) {
        Permission::findOrCreate($permissionName, 'web');
    }

    $user->givePermissionTo($permissions);
}

function mondayDate(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

function appointmentManager(): User
{
    $user = User::factory()->create();
    grantPermissions($user, ['manage_appointments', 'read_all_appointments']);

    return $user;
}

test('appointment store rejects overlapping active appointments', function () {
    $assistant = appointmentManager();
    Sanctum::actingAs($assistant);

    $doctor = Doctor::factory()->create();
    $date = mondayDate();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'scheduled',
    ]);

    $response = $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30:00',
        'appointment_end_time' => '11:30:00',
        'status' => 'scheduled',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_start_time']);
});

test('appointment store ignores canceled appointments for conflict detection', function () {
    $assistant = appointmentManager();
    Sanctum::actingAs($assistant);

    $doctor = Doctor::factory()->create();
    $date = mondayDate();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'canceled',
    ]);

    $response = $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30:00',
        'appointment_end_time' => '11:30:00',
        'status' => 'scheduled',
    ]);

    $response->assertCreated();
});

test('appointment update applies schedule and conflict validation with merged values', function () {
    $assistant = appointmentManager();
    Sanctum::actingAs($assistant);

    $doctor = Doctor::factory()->create();
    $date = mondayDate();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '09:00:00',
        'appointment_end_time' => '10:00:00',
        'status' => 'scheduled',
    ]);

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30:00',
        'appointment_end_time' => '11:30:00',
        'status' => 'scheduled',
    ]);

    $this->putJson("/api/v1/appointments/{$appointment->id}", [
        'appointment_start_time' => '10:45:00',
        'appointment_end_time' => '11:15:00',
    ])->assertStatus(422)->assertJsonValidationErrors(['appointment_start_time']);

    $this->putJson("/api/v1/appointments/{$appointment->id}", [
        'appointment_start_time' => '08:00:00',
        'appointment_end_time' => '08:30:00',
    ])->assertStatus(422)->assertJsonValidationErrors(['appointment_start_time']);

    $this->putJson("/api/v1/appointments/{$appointment->id}", [
        'appointment_start_time' => '09:00:00',
        'appointment_end_time' => '10:00:00',
    ])->assertOk();
});
