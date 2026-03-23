<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Patient::class, 'patient');
    }

    public function index()
    {
        return response()->json(Patient::latest()->get());
    }

    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json([
            'message' => 'Paciente eliminado correctamente',
        ]);
    }
}
