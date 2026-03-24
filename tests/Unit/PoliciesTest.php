<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\PatientPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\UserPolicy;

test('appointment policy permite viewAny a quien tiene permiso de lectura', function () {
    // La policy debe habilitar el listado a perfiles que leen citas.
    $policy = new AppointmentPolicy();
    $user = createUserWithPermissions(['read_own_appointments']);

    expect($policy->viewAny($user))->toBeTrue();
});

test('appointment policy permite ver una cita propia al doctor asignado', function () {
    // Un doctor lector debe poder consultar sus propias citas.
    $policy = new AppointmentPolicy();
    $user = createUserWithPermissions(['read_own_appointments']);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id]);

    expect($policy->view($user, $appointment))->toBeTrue();
});

test('appointment policy niega ver citas de otro doctor', function () {
    // Esto evita acceso cruzado entre agendas.
    $policy = new AppointmentPolicy();
    $user = createUserWithPermissions(['read_appointments']);
    Doctor::factory()->create(['user_id' => $user->id]);
    $appointment = Appointment::factory()->create();

    expect($policy->view($user, $appointment))->toBeFalse();
});

test('appointment policy permite crear cuando existe manage appointments', function () {
    // La escritura sobre citas debe depender del permiso de gestion.
    $policy = new AppointmentPolicy();
    $user = createUserWithPermissions(['manage_appointments']);

    expect($policy->create($user))->toBeTrue();
});

test('appointment policy solo permite ver citas por paciente con permiso global', function () {
    // La consulta por paciente debe ser mas restrictiva.
    $policy = new AppointmentPolicy();
    $reader = createUserWithPermissions(['read_appointments']);
    $manager = createUserWithPermissions(['read_all_appointments']);

    expect($policy->viewPatientAppointments($reader))->toBeFalse();
    expect($policy->viewPatientAppointments($manager))->toBeTrue();
});

test('schedule policy permite viewAny a quien gestiona citas', function () {
    // Gestionar citas tambien debe abrir acceso al listado de horarios.
    $policy = new SchedulePolicy();
    $user = createUserWithPermissions(['manage_appointments']);

    expect($policy->viewAny($user))->toBeTrue();
});

test('schedule policy permite ver un horario propio al doctor lector', function () {
    // El doctor debe poder ver su propia disponibilidad.
    $policy = new SchedulePolicy();
    $user = createUserWithPermissions(['read_own_appointments']);
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);
    $schedule = Schedule::factory()->create(['doctor_id' => $doctor->id]);

    expect($policy->view($user, $schedule))->toBeTrue();
});

test('schedule policy permite consultar horarios de cualquier doctor a un gestor', function () {
    // Un perfil administrativo debe pasar por la policy especial del endpoint.
    $policy = new SchedulePolicy();
    $user = createUserWithPermissions(['manage_appointments']);

    expect($policy->viewDoctorSchedules($user, 999))->toBeTrue();
});

test('schedule policy niega eliminar horarios sin permiso de gestion', function () {
    // Borrar disponibilidad debe mantenerse restringido.
    $policy = new SchedulePolicy();
    $user = createUserWithPermissions(['read_appointments']);
    $schedule = Schedule::factory()->create();

    expect($policy->delete($user, $schedule))->toBeFalse();
});

test('patient policy permite listar pacientes con permiso de lectura', function () {
    // No todo acceso a pacientes implica edicion.
    $policy = new PatientPolicy();
    $user = createUserWithPermissions(['read_patients']);

    expect($policy->viewAny($user))->toBeTrue();
});

test('patient policy niega actualizar pacientes a un usuario de solo lectura', function () {
    // La separacion entre lectura y escritura debe respetarse en la policy.
    $policy = new PatientPolicy();
    $user = createUserWithPermissions(['read_patients']);
    $patient = Patient::factory()->create();

    expect($policy->update($user, $patient))->toBeFalse();
});

test('doctor policy permite listar doctores a quien administra usuarios', function () {
    // El mantenimiento de doctores cae en perfiles administrativos.
    $policy = new DoctorPolicy();
    $user = createUserWithPermissions(['manage_users']);

    expect($policy->viewAny($user))->toBeTrue();
});

test('doctor policy permite ver el doctor asociado al mismo usuario', function () {
    // Aun sin permiso global, un doctor puede verse a si mismo.
    $policy = new DoctorPolicy();
    $user = createUserWithPermissions();
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);

    expect($policy->view($user, $doctor))->toBeTrue();
});

test('doctor policy permite actualizar el doctor asociado al mismo usuario', function () {
    // La policy actual deja autoedicion para el doctor propietario.
    $policy = new DoctorPolicy();
    $user = createUserWithPermissions();
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);

    expect($policy->update($user, $doctor))->toBeTrue();
});

test('doctor policy niega eliminar si solo es dueno del perfil medico', function () {
    // La eliminacion sigue reservada al permiso administrativo.
    $policy = new DoctorPolicy();
    $user = createUserWithPermissions();
    $doctor = Doctor::factory()->create(['user_id' => $user->id]);

    expect($policy->delete($user, $doctor))->toBeFalse();
});

test('medical record policy permite ver con permiso medico administrativo', function () {
    // El expediente debe quedar bajo un permiso explicito.
    $policy = new MedicalRecordPolicy();
    $user = createUserWithPermissions(['manage_medical_records']);
    $record = MedicalRecord::factory()->create();

    expect($policy->view($user, $record))->toBeTrue();
});

test('medical record policy niega actualizar sin permiso', function () {
    // Sin permiso dedicado no deberia haber edicion clinica.
    $policy = new MedicalRecordPolicy();
    $user = createUserWithPermissions();
    $record = MedicalRecord::factory()->create();

    expect($policy->update($user, $record))->toBeFalse();
});

test('user policy permite listar usuarios a quien administra usuarios', function () {
    // La administracion del modulo de usuarios depende de un permiso unico.
    $policy = new UserPolicy();
    $user = createUserWithPermissions(['manage_users']);

    expect($policy->viewAny($user))->toBeTrue();
});

test('user policy niega eliminar usuarios sin manage users', function () {
    // Sin permiso administrativo no debe permitirse el borrado.
    $policy = new UserPolicy();
    $user = createUserWithPermissions();
    $otherUser = User::factory()->create();

    expect($policy->delete($user, $otherUser))->toBeFalse();
});
