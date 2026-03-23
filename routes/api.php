<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;


Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    // Pacientes
    Route::apiResource('patients', PatientController::class)->middleware('auth:sanctum');

    // Expedientes
    Route::get('/patients/{patient}/medical-record', [MedicalRecordController::class, 'show'])
        ->middleware('auth:sanctum');
    Route::post('/patients/{patient}/medical-record', [MedicalRecordController::class, 'store'])
        ->middleware('auth:sanctum');
    Route::put('/patients/{patient}/medical-record', [MedicalRecordController::class, 'update'])
        ->middleware('auth:sanctum');


    // Doctores
    Route::apiResource('doctors', DoctorController::class)->middleware('auth:sanctum');

    // Horarios
    Route::apiResource('schedules', ScheduleController::class)->middleware('auth:sanctum');
    Route::get('/doctors/{doctor}/schedules', [ScheduleController::class, 'doctorSchedules'])->middleware('auth:sanctum');

    // Citas
    Route::apiResource('appointments', AppointmentController::class)->middleware('auth:sanctum');
    Route::get('/doctors/{doctor}/appointments', [AppointmentController::class, 'doctorAppointments'])->middleware('auth:sanctum');
    Route::get('/patients/{patient}/appointments', [AppointmentController::class, 'patientAppointments'])->middleware('auth:sanctum');

    // Usuarios
    Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
});