<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;


class AppointmentController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Appointment::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasPermissionTo('read_appointments') && !$user->hasPermissionTo('read_all_appointments')) {
            return response()->json(
                Appointment::where('doctor_id', $user->doctor?->id)->latest()->get()
            );
        }

        return response()->json(Appointment::latest()->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Appointment::class);

        $validated = $request->validate([
            'patient_id' => ['required', 'integer'],
            'doctor_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date'],
            'appointment_start_time' => ['required'],
            'appointment_end_time' => ['required'],
            'reason' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:20'],
        ]);

        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');

        $isAvailable = Schedule::where('doctor_id', $validated['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $validated['appointment_start_time'])
            ->where('end_time', '>=', $validated['appointment_end_time'])
            ->exists();

        if (!$isAvailable) {
            return response()->json([
                'message' => 'El doctor no tiene disponibilidad programada para este día u horario.'
            ], 422);
        }

        $hasConflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('appointment_start_time', [$validated['appointment_start_time'], $validated['appointment_end_time']])
                    ->orWhereBetween('appointment_end_time', [$validated['appointment_start_time'], $validated['appointment_end_time']]);
            })
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'El doctor ya tiene una cita asignada en este rango.'], 422);
        }

        $appointment = Appointment::create([
            ...$validated,
            'status' => $validated['status'] ?? 'scheduled',
        ]);

        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('view', $appointment);

        return response()->json($appointment);
    }

    public function update(Request $request, Appointment $appointment)
    {
        Gate::authorize('update', $appointment);

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
        Gate::authorize('delete', $appointment);

        $appointment->delete();

        return response()->json([
            'message' => 'Cita eliminada correctamente'
        ]);
    }

    public function doctorAppointments($doctorId)
    {
        Gate::authorize('viewDoctorAppointments', [Appointment::class, (int)$doctorId]);

        $appointments = Appointment::where('doctor_id', $doctorId)->get();

        return response()->json($appointments);
    }

    public function patientAppointments($patientId)
    {
        Gate::authorize('viewPatientAppointments', Appointment::class);

        $appointments = Appointment::where('patient_id', $patientId)->get();

        return response()->json($appointments);
    }
}
