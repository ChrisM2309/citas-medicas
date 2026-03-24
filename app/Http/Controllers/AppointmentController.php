<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Appointments\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Appointment::class, 'appointment');
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json(
            $this->visibleAppointmentsQuery($user)
                ->latest()
                ->get(),
        );
    }

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment->update($request->validated());

        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json([
            'message' => 'Cita eliminada correctamente',
        ]);
    }

    public function doctorAppointments($doctorId)
    {
        Gate::authorize('viewDoctorAppointments', [Appointment::class, (int) $doctorId]);

        /** @var User $user */
        $user = Auth::user();

        $appointments = $this->visibleAppointmentsQuery($user)
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();

        return response()->json($appointments);
    }

    public function patientAppointments($patientId)
    {
        Gate::authorize('viewPatientAppointments', Appointment::class);

        /** @var User $user */
        $user = Auth::user();

        $appointments = $this->visibleAppointmentsQuery($user)
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        return response()->json($appointments);
    }

    private function visibleAppointmentsQuery(User $user): Builder
    {
        $query = Appointment::query();

        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return $query;
        }

        if ($user->hasPermissionTo('read_own_appointments')) {
            return $query->where('doctor_id', $user->doctor?->id ?? 0);
        }

        return $query->whereRaw('1 = 0');
    }
}