<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Services\AppointmentValidationService;

test('normaliza horas en formato hh mm', function () {
    // El servicio debe aceptar horas cortas porque asi llegan desde varios formularios.
    $service = app(AppointmentValidationService::class);

    expect($service->normalizeTime('09:30'))->toBe('09:30:00');
});

test('normaliza horas en formato hh mm ss', function () {
    // Tambien se cubre el formato completo usado por la base de datos.
    $service = app(AppointmentValidationService::class);

    expect($service->normalizeTime('09:30:15'))->toBe('09:30:15');
});

test('retorna null cuando la hora esta vacia', function () {
    // Un valor vacio no es valido.
    $service = app(AppointmentValidationService::class);

    expect($service->normalizeTime(''))->toBeNull();
});

test('retorna null cuando la hora tiene un formato invalido', function () {
    // La validacion interna debe ser estricta con entradas no soportadas.
    $service = app(AppointmentValidationService::class);

    expect($service->normalizeTime('9 AM'))->toBeNull();
});

test('detecta cuando una cita cae dentro del horario del doctor', function () {
    // Este es el caso ideal.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => nextMonday(),
        'appointment_start_time' => '09:00',
        'appointment_end_time' => '10:00',
    ];

    expect($service->isWithinDoctorSchedule($payload))->toBeTrue();
});

test('rechaza disponibilidad cuando la fecha es invalida', function () {
    // Si la fecha no puede interpretarse, el servicio debe fallar de forma segura.
    $service = app(AppointmentValidationService::class);

    $payload = [
        'doctor_id' => 1,
        'appointment_date' => 'fecha-invalida',
        'appointment_start_time' => '09:00',
        'appointment_end_time' => '10:00',
    ];

    expect($service->isWithinDoctorSchedule($payload))->toBeFalse();
});

test('rechaza disponibilidad cuando la cita queda fuera del horario', function () {
    // Aseguramos que no baste con el dia correcto; el rango de hora tambien importa.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();

    Schedule::factory()->create([
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Monday',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => nextMonday(),
        'appointment_start_time' => '12:30',
        'appointment_end_time' => '13:00',
    ];

    expect($service->isWithinDoctorSchedule($payload))->toBeFalse();
});

test('detecta conflicto cuando dos citas se solapan', function () {
    // Protegemos la regla de no reservar al mismo doctor al mismo tiempo.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'scheduled',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:30',
        'appointment_end_time' => '11:30',
    ];

    expect($service->hasConflict($payload))->toBeTrue();
});

test('no detecta conflicto cuando una cita inicia justo al terminar otra', function () {
    // Los rangos contiguos deben ser validos.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'scheduled',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => $date,
        'appointment_start_time' => '11:00',
        'appointment_end_time' => '12:00',
    ];

    expect($service->hasConflict($payload))->toBeFalse();
});

test('ignora citas canceladas al buscar conflictos', function () {
    // Una reserva cancelada no debe bloquear espacio en agenda.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'canceled',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:15',
        'appointment_end_time' => '10:45',
    ];

    expect($service->hasConflict($payload))->toBeFalse();
});

test('puede ignorar una cita especifica al validar conflictos', function () {
    // Esto permite editar una cita sin que choque consigo misma.
    $service = app(AppointmentValidationService::class);
    $doctor = Doctor::factory()->create();
    $date = nextMonday();

    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => Patient::factory()->create()->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00:00',
        'appointment_end_time' => '11:00:00',
        'status' => 'scheduled',
    ]);

    $payload = [
        'doctor_id' => $doctor->id,
        'appointment_date' => $date,
        'appointment_start_time' => '10:00',
        'appointment_end_time' => '11:00',
    ];

    expect($service->hasConflict($payload, $appointment))->toBeFalse();
});