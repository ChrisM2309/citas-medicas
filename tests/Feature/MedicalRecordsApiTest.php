<?php

use App\Models\MedicalRecord;
use App\Models\Patient;

test('el expediente medico requiere autenticacion para consultarse', function () {
    // Aunque no hay policy aplicada aqui, la ruta si esta protegida por Sanctum.
    $patient = Patient::factory()->create();

    $this->getJson("/api/v1/patients/{$patient->id}/medical-record")
        ->assertUnauthorized();
});

test('se puede consultar el expediente medico de un paciente autenticado', function () {
    // Documentamos el comportamiento actual del endpoint show.
    authenticateWithPermissions();
    $patient = Patient::factory()->create();
    $record = MedicalRecord::factory()->create([
        'patient_id' => $patient->id,
        'blood_type' => 'O+',
    ]);

    $this->getJson("/api/v1/patients/{$patient->id}/medical-record")
        ->assertOk()
        ->assertJsonPath('id', $record->id)
        ->assertJsonPath('blood_type', 'O+');
});

test('se puede crear un expediente usando el alias cronic_diseases', function () {
    // Cubrimos la normalizacion especial implementada en el controlador.
    authenticateWithPermissions();
    $patient = Patient::factory()->create();

    $this->postJson("/api/v1/patients/{$patient->id}/medical-record", [
        'blood_type' => 'AB+',
        'cronic_diseases' => 'Diabetes',
    ])
        ->assertCreated()
        ->assertJsonPath('chronic_diseases', 'Diabetes');

    $this->assertDatabaseHas('medical_records', [
        'patient_id' => $patient->id,
        'chronic_diseases' => 'Diabetes',
    ]);
});

test('se puede actualizar un expediente medico existente', function () {
    // Verificamos la persistencia de cambios clinicos.
    authenticateWithPermissions();
    $patient = Patient::factory()->create();
    $record = MedicalRecord::factory()->create([
        'patient_id' => $patient->id,
        'blood_type' => 'A+',
    ]);

    $this->putJson("/api/v1/patients/{$patient->id}/medical-record", [
        'blood_type' => 'B+',
        'allergies' => 'Penicilina',
    ])
        ->assertOk()
        ->assertJsonPath('id', $record->id)
        ->assertJsonPath('blood_type', 'B+');
});

test('el expediente valida la longitud maxima del tipo de sangre', function () {
    // Una validacion simple evita guardar valores inconsistentes.
    authenticateWithPermissions();
    $patient = Patient::factory()->create();

    $this->postJson("/api/v1/patients/{$patient->id}/medical-record", [
        'blood_type' => 'AB++',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['blood_type']);
});
