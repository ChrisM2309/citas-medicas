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

function createDoctorUserWithReadPermission(): array
{
    Permission::findOrCreate('read_appointments', 'web');

    $user = User::factory()->create();
    $user->givePermissionTo('read_appointments');

    $doctor = Doctor::factory()->create([
        'user_id' => $user->id,
    ]);

    return [$user, $doctor];
}

test('doctor sees only own appointments on index and doctor endpoint', function () {
    [$doctorUser, $ownDoctor] = createDoctorUserWithReadPermission();
    [$_, $otherDoctor] = createDoctorUserWithReadPermission();

    Sanctum::actingAs($doctorUser);

    Appointment::factory()->create([
        'doctor_id' => $ownDoctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'status' => 'scheduled',
    ]);

    Appointment::factory()->create([
        'doctor_id' => $otherDoctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'status' => 'scheduled',
    ]);

    $indexResponse = $this->getJson('/api/v1/appointments')
        ->assertOk();

    expect($indexResponse->json())->toHaveCount(1);
    expect($indexResponse->json('0.doctor_id'))->toBe($ownDoctor->id);

    $ownResponse = $this->getJson("/api/v1/doctors/{$ownDoctor->id}/appointments")
        ->assertOk();

    expect($ownResponse->json())->toHaveCount(1);
    expect($ownResponse->json('0.doctor_id'))->toBe($ownDoctor->id);

    $this->getJson("/api/v1/doctors/{$otherDoctor->id}/appointments")
        ->assertForbidden();
});

test('doctor sees only own schedules on index and doctor endpoint', function () {
    [$doctorUser, $ownDoctor] = createDoctorUserWithReadPermission();
    [$_, $otherDoctor] = createDoctorUserWithReadPermission();

    Sanctum::actingAs($doctorUser);

    Schedule::factory()->create([
        'doctor_id' => $ownDoctor->id,
        'day_of_week' => 'Monday',
    ]);

    Schedule::factory()->create([
        'doctor_id' => $otherDoctor->id,
        'day_of_week' => 'Tuesday',
    ]);

    $indexResponse = $this->getJson('/api/v1/schedules')
        ->assertOk();

    expect($indexResponse->json())->toHaveCount(1);
    expect($indexResponse->json('0.doctor_id'))->toBe($ownDoctor->id);

    $ownResponse = $this->getJson("/api/v1/doctors/{$ownDoctor->id}/schedules")
        ->assertOk();

    expect($ownResponse->json())->toHaveCount(1);
    expect($ownResponse->json('0.doctor_id'))->toBe($ownDoctor->id);

    $this->getJson("/api/v1/doctors/{$otherDoctor->id}/schedules")
        ->assertForbidden();
});
