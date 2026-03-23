<?php

use App\Models\Patient;

test('el listado de pacientes exige permiso de lectura o gestion', function () {
    // Un usuario autenticado sin permisos de pacientes no debe poder ver el modulo.
    authenticateWithPermissions();

    $this->getJson('/api/v1/patients')
        ->assertForbidden();
});

test('el listado de pacientes devuelve registros para usuarios con permiso', function () {
    // Validamos que el modulo responda correctamente cuando el permiso existe.
    authenticateWithPermissions(['manage_patients']);

    $newest = Patient::factory()->create(['email' => 'nuevo@example.com']);
    $oldest = Patient::factory()->create(['email' => 'viejo@example.com']);

    $response = $this->getJson('/api/v1/patients')
        ->assertOk();

    expect($response->json())->toHaveCount(2);
    expect(collect($response->json())->pluck('id')->all())->toContain($newest->id, $oldest->id);
});

test('se puede crear un paciente con permiso de gestion', function () {
    // Cubrimos el alta principal de pacientes.
    authenticateWithPermissions(['manage_patients']);

    $this->postJson('/api/v1/patients', [
        'name' => 'Maria',
        'lastname' => 'Lopez',
        'email' => 'maria@example.com',
        'phone' => '71234567',
        'birth_date' => '1995-04-10',
        'gender' => 'F',
    ])
        ->assertCreated()
        ->assertJsonPath('email', 'maria@example.com');

    $this->assertDatabaseHas('patients', [
        'email' => 'maria@example.com',
        'lastname' => 'Lopez',
    ]);
});

test('no se puede crear un paciente con correo repetido', function () {
    // Evitamos duplicados de un dato sensible y muy usado para busquedas.
    authenticateWithPermissions(['manage_patients']);
    Patient::factory()->create(['email' => 'maria@example.com']);

    $this->postJson('/api/v1/patients', [
        'name' => 'Maria',
        'lastname' => 'Lopez',
        'email' => 'maria@example.com',
        'gender' => 'F',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('se puede consultar un paciente con permiso de lectura', function () {
    // Confirmamos que el detalle del paciente sea accesible para perfiles de consulta.
    authenticateWithPermissions(['read_patients']);
    $patient = Patient::factory()->create();

    $this->getJson("/api/v1/patients/{$patient->id}")
        ->assertOk()
        ->assertJsonPath('id', $patient->id);
});

test('el permiso de solo lectura no permite actualizar pacientes', function () {
    // Comprobamos que lectura y escritura esten separadas.
    authenticateWithPermissions(['read_patients']);
    $patient = Patient::factory()->create();

    $this->putJson("/api/v1/patients/{$patient->id}", [
        'name' => 'Nombre editado',
    ])
        ->assertForbidden();
});

test('se puede actualizar un paciente conservando su mismo correo', function () {
    // La regla unique debe ignorar el registro actual al editar.
    authenticateWithPermissions(['manage_patients']);
    $patient = Patient::factory()->create(['email' => 'paciente@example.com']);

    $this->putJson("/api/v1/patients/{$patient->id}", [
        'name' => 'Paciente editado',
        'email' => 'paciente@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('name', 'Paciente editado');
});

test('el endpoint destroy realiza soft delete del paciente', function () {
    // Protegemos la trazabilidad del historial al eliminar pacientes.
    authenticateWithPermissions(['manage_patients']);
    $patient = Patient::factory()->create();

    $this->deleteJson("/api/v1/patients/{$patient->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Paciente eliminado correctamente');

    $this->assertSoftDeleted('patients', ['id' => $patient->id]);
});
