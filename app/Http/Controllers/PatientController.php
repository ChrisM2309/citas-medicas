<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Patient::class);

        return response()->json(Patient::latest()->get());
    }

    public function store(StorePatientRequest $request)
    {
        Gate::authorize('create', Patient::class);

        $patient = Patient::create($request->validated());

        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);

        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        Gate::authorize('update', $patient);

        $patient->update($request->validated());

        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);

        $patient->delete();

        return response()->json([
            'message' => 'Paciente eliminado correctamente',
        ]);
    }
}
