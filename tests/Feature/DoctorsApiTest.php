<?php

use App\Models\Doctor;
use App\Models\User;

test('el listado de doctores requiere autenticacion', function () {
    // Aunque este modulo no aplica policy explicita, si debe exigir sesion.
    $this->getJson('/api/v1/doctors')
        ->assertUnauthorized();
});

test('el listado de doctores devuelve registros al usuario autenticado', function () {
    // Documentamos el comportamiento actual del controlador.
    authenticateWithPermissions();
    $doctorA = Doctor::factory()->create();
    $doctorB = Doctor::factory()->create();

    $response = $this->getJson('/api/v1/doctors')
        ->assertOk();

    expect($response->json())->toHaveCount(2);
    expect(collect($response->json())->pluck('id')->all())->toContain($doctorA->id, $doctorB->id);
});

test('se puede crear un doctor con un usuario valido', function () {
    // Cubrimos el alta basica de doctores y su relacion con usuarios.
    authenticateWithPermissions();
    $user = User::factory()->create();

    $this->postJson('/api/v1/doctors', [
        'user_id' => $user->id,
        'specialty' => 'Cardiology',
        'phone' => '72345678',
    ])
        ->assertCreated()
        ->assertJsonPath('user_id', $user->id);
});

test('no se puede crear un doctor con un user_id repetido', function () {
    // La relacion uno a uno entre usuario y doctor debe respetarse.
    authenticateWithPermissions();
    $user = User::factory()->create();
    Doctor::factory()->create(['user_id' => $user->id]);

    $this->postJson('/api/v1/doctors', [
        'user_id' => $user->id,
        'specialty' => 'Neurology',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['user_id']);
});

test('se puede consultar el detalle de un doctor autenticado', function () {
    // Confirmamos que el show exponga el recurso esperado.
    authenticateWithPermissions();
    $doctor = Doctor::factory()->create();

    $this->getJson("/api/v1/doctors/{$doctor->id}")
        ->assertOk()
        ->assertJsonPath('id', $doctor->id);
});

test('se puede actualizar un doctor autenticado', function () {
    // Cubrimos la edicion de especialidad y telefono.
    authenticateWithPermissions();
    $doctor = Doctor::factory()->create();

    $this->putJson("/api/v1/doctors/{$doctor->id}", [
        'specialty' => 'Pediatrics',
        'phone' => '70001111',
    ])
        ->assertOk()
        ->assertJsonPath('specialty', 'Pediatrics');
});

test('el endpoint destroy realiza soft delete del doctor', function () {
    // Verificamos que la eliminacion no borre fisicamente el registro.
    authenticateWithPermissions();
    $doctor = Doctor::factory()->create();

    $this->deleteJson("/api/v1/doctors/{$doctor->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Doctor eliminado correctamente');

    $this->assertSoftDeleted('doctors', ['id' => $doctor->id]);
});
