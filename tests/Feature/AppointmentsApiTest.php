<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;

test('el listado de citas exige permisos de lectura', function () {
    // Un usuario autenticado sin permiso no debe ver citas.
    authenticateWithPermissions();

    $this->getJson('/api/v1/appointments')
        ->assertForbidden();
});

test('un gestor puede ver todas las citas', function () {
    // El personal administrativo con permiso global debe ver todo el calendario.
    authenticateWithPermissions(['read_all_appointments']);
    $appointmentA = Appointment::factory()->create();
    $appointmentB = Appointment::factory()->create();

    $response = $this->getJson('/api/v1/appointments')
        ->assertOk();

    expect($response->json())->toHaveCount(2);
    expect(collect($response->json())->pluck('id')->all())->toContain($appointmentA->id, $appointmentB->id);
});

test('un doctor solo ve sus propias citas en el index', function () {
    // La policy debe acotar la vista de un doctor a su agenda.
    $user = authenticateWithPermissions(['read_appointments']);
    $ownDoctor = Doctor::factory()->create(['user_id' => $user->id]);
    $otherDoctor = Doctor::factory()->create();

    $ownAppointment = Appointment::factory()->create(['doctor_id' => $ownDoctor->id]);
    Appointment::factory()->create(['doctor_id' => $otherDoctor->id]);

    $response = $this->getJson('/api/v1/appointments')
        ->assertOk();

    expect($response->json())->toHaveCount(1);
    expect($response->json('0.id'))->toBe($ownAppointment->id);
});

test('un doctor puede consultar una cita propia', function () {
    // El detalle de una cita propia debe estar disponible para el medico asignado.
    $user = authenticateWithPermissions(['read_appointments']);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id]);

    $this->getJson("/api/v1/appointments/{$appointment->id}")
        ->assertOk()
        ->assertJsonPath('id', $appointment->id);
});

test('un doctor no puede consultar una cita de otro doctor', function () {
    // Evitamos fugas de informacion entre agendas medicas.
    $user = authenticateWithPermissions(['read_appointments']);
    Doctor::factory()->create(['user_id' => $user->id]);
    $appointment = Appointment::factory()->create();

    $this->getJson("/api/v1/appointments/{$appointment->id}")
        ->assertForbidden();
});

test('un doctor puede consultar sus citas por endpoint de doctor', function () {
    // El endpoint filtrado por doctor debe devolver solo lo suyo.
    $user = authenticateWithPermissions(['read_appointments']);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    Appointment::factory()->create(['doctor_id' => $doctor->id]);

    $this->getJson("/api/v1/doctors/{$doctor->id}/appointments")
        ->assertOk()
        ->assertJsonCount(1);
});

test('un doctor no puede consultar las citas de otro doctor', function () {
    // Se refuerza el mismo aislamiento en el endpoint especializado.
    $user = authenticateWithPermissions(['read_appointments']);
    Doctor::factory()->create(['user_id' => $user->id]);
    $otherDoctor = Doctor::factory()->create();

    $this->getJson("/api/v1/doctors/{$otherDoctor->id}/appointments")
        ->assertForbidden();
});

test('las citas por paciente requieren permiso global', function () {
    // Solo perfiles administrativos deben navegar transversalmente por paciente.
    authenticateWithPermissions(['read_appointments']);
    $patient = Patient::factory()->create();

    $this->getJson("/api/v1/patients/{$patient->id}/appointments")
        ->assertForbidden();
});

test('crear citas requiere permiso de gestion', function () {
    // Agendar citas no debe estar permitido para un doctor solo lector.
    authenticateWithPermissions(['read_appointments']);
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date,
        'appointment_start_time' => '09:00',
        'appointment_end_time' => '10:00',
    ])
        ->assertForbidden();
});

test('se puede crear una cita valida y el estado por defecto es scheduled', function () {
    // Verificamos el caso feliz del modulo de citas.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date,
        'appointment_start_time' => '09:00',
        'appointment_end_time' => '10:00',
        'reason' => 'Chequeo general',
    ])
        ->assertCreated()
        ->assertJsonPath('status', 'scheduled')
        ->assertJsonPath('reason', 'Chequeo general');
});

test('no se puede crear una cita fuera del horario del doctor', function () {
    // La validacion debe respetar la agenda configurada.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date,
        'appointment_start_time' => '12:30',
        'appointment_end_time' => '13:00',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_start_time']);
});

test('no se puede crear una cita solapada con otra activa', function () {
    // Evitamos doble reserva para el mismo doctor.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
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

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30',
        'appointment_end_time' => '11:30',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_start_time']);
});

test('las citas canceladas no generan conflicto al crear una nueva', function () {
    // Una reserva cancelada no debe bloquear espacio utilizable.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
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

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30',
        'appointment_end_time' => '11:30',
    ])
        ->assertCreated();
});

test('no se puede crear una cita con hora final anterior a la inicial', function () {
    // Aseguramos una validacion temporal basica antes de persistir.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $patient = Patient::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $this->postJson('/api/v1/appointments', [
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => $date,
        'appointment_start_time' => '11:00',
        'appointment_end_time' => '10:00',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_end_time']);
});

test('al actualizar una cita se validan conflictos usando valores combinados', function () {
    // Cubrimos el caso donde el update envia solo parte del payload.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
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
        'appointment_start_time' => '10:45',
        'appointment_end_time' => '11:15',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['appointment_start_time']);
});

test('al actualizar una cita se puede mover dentro del horario disponible', function () {
    // Tambien validamos el camino feliz del update.
    authenticateWithPermissions(['manage_appointments']);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
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

    $this->putJson("/api/v1/appointments/{$appointment->id}", [
        'appointment_start_time' => '10:00',
        'appointment_end_time' => '10:30',
        'reason' => 'Reprogramada',
    ])
        ->assertOk()
        ->assertJsonPath('reason', 'Reprogramada');
});

test('el endpoint destroy realiza soft delete de la cita', function () {
    // El historial de citas debe conservarse incluso al eliminar.
    authenticateWithPermissions(['manage_appointments']);
    $appointment = Appointment::factory()->create();

    $this->deleteJson("/api/v1/appointments/{$appointment->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Cita eliminada correctamente');

    $this->assertSoftDeleted('appointments', ['id' => $appointment->id]);
});
