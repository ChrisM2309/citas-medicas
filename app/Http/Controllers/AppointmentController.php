<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return response()->json(Appointment::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'doctor_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date'],
            'appointment_start_time' => ['required'],
            'appointment_end_time' => ['required'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:20'],
        ]);

        //  TODO 
        // 1) validación de horario del doctor
        // 2) validación de conflicto de cita
        // 3) policies/authorize

        $appointment = Appointment::create([
            ...$validated,
            'status' => $validated['status'] ?? 'scheduled',
        ]);

        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => ['sometimes', 'integer'],
            'doctor_id' => ['sometimes', 'integer'],
            'appointment_date' => ['sometimes', 'date'],
            'appointment_start_time' => ['sometimes'],
            'appointment_end_time' => ['sometimes'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:20'],
        ]);

        $appointment->update($validated);

        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json([
            'message' => 'Cita eliminada correctamente'
        ]);
    }

    public function doctorAppointments($doctorId)
    {
        $appointments = Appointment::where('doctor_id', $doctorId)->get();

        return response()->json($appointments);
    }

    public function patientAppointments($patientId)
    {
        $appointments = Appointment::where('patient_id', $patientId)->get();

        return response()->json($appointments);
    }
}
