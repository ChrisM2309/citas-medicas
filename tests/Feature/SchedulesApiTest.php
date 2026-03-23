<?php

use App\Models\Doctor;
use App\Models\Schedule;

test('el listado de horarios exige permisos relacionados a citas', function () {
    // Un usuario sin permisos de agenda no debe ver horarios.
    authenticateWithPermissions();

    $this->getJson('/api/v1/schedules')
        ->assertForbidden();
});

test('un gestor de citas puede ver todos los horarios', function () {
    // Administrativos con permiso global deben tener visibilidad completa.
    authenticateWithPermissions(['manage_appointments', 'read_all_appointments']);
    $scheduleA = Schedule::factory()->create();
    $scheduleB = Schedule::factory()->create();

    $response = $this->getJson('/api/v1/schedules')
        ->assertOk();

    expect($response->json())->toHaveCount(2);
    expect(collect($response->json())->pluck('id')->all())->toContain($scheduleA->id, $scheduleB->id);
});

test('un doctor solo ve sus propios horarios en el index', function () {
    // La policy debe filtrar el listado al doctor autenticado.
    $user = authenticateWithPermissions(['read_appointments']);
    $ownDoctor = Doctor::factory()->create(['user_id' => $user->id]);
    $otherDoctor = Doctor::factory()->create();

    $ownSchedule = Schedule::factory()->create(['doctor_id' => $ownDoctor->id]);
    Schedule::factory()->create(['doctor_id' => $otherDoctor->id]);

    $response = $this->getJson('/api/v1/schedules')
        ->assertOk();

    expect($response->json())->toHaveCount(1);
    expect($response->json('0.id'))->toBe($ownSchedule->id);
});

test('un doctor puede consultar sus horarios por endpoint especifico', function () {
    // El endpoint por doctor debe funcionar para el medico dueño del recurso.
    $user = authenticateWithPermissions(['read_appointments']);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    Schedule::factory()->create(['doctor_id' => $doctor->id, 'day_of_week' => 'Monday']);

    $this->getJson("/api/v1/doctors/{$doctor->id}/schedules")
        ->assertOk()
        ->assertJsonCount(1);
});

test('un doctor no puede consultar los horarios de otro doctor', function () {
    // Reforzamos el aislamiento entre agendas medicas.
    $user = authenticateWithPermissions(['read_appointments']);
    Doctor::factory()->create(['user_id' => $user->id]);
    $otherDoctor = Doctor::factory()->create();

    $this->getJson("/api/v1/doctors/{$otherDoctor->id}/schedules")
        ->assertForbidden();
});

test('crear horarios requiere permiso de gestion de citas', function () {
    // Crear bloques de agenda es una operacion administrativa.
    authenticateWithPermissions(['read_appointments']);
    $doctor = Doctor::factory()->create();

    $this->postJson('/api/v1/schedules', [
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00',
        'end_time' => '12:00',
    ])
        ->assertForbidden();
});

test('se puede crear un horario valido', function () {
    // Cubrimos el alta correcta de disponibilidad.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();

    $this->postJson('/api/v1/schedules', [
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00',
        'end_time' => '12:00',
    ])
        ->assertCreated()
        ->assertJsonPath('day_of_week', 'Monday');
});

test('no se puede crear un horario con hora final anterior a la inicial', function () {
    // Evitamos rangos invalidos que romperian la logica de disponibilidad.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();

    $this->postJson('/api/v1/schedules', [
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '12:00',
        'end_time' => '08:00',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['end_time']);
});

test('actualizar horario valida que el dia de la semana sea permitido', function () {
    // La agenda solo acepta los dias definidos por la regla de negocio actual.
    authenticateWithPermissions(['manage_appointments']);
    $schedule = Schedule::factory()->create();

    $this->putJson("/api/v1/schedules/{$schedule->id}", [
        'day_of_week' => 'Lunes',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['day_of_week']);
});

test('el endpoint destroy realiza soft delete del horario', function () {
    // La agenda eliminada debe permanecer trazable en base de datos.
    authenticateWithPermissions(['manage_appointments']);
    $schedule = Schedule::factory()->create();

    $this->deleteJson("/api/v1/schedules/{$schedule->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Horario eliminado correctamente');

    $this->assertSoftDeleted('schedules', ['id' => $schedule->id]);
});
