<?php

namespace App\Http\Controllers;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Patient::class);
        return response()->json(Patient::latest()->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Patient::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:patients,email'],
            'phone' => ['nullable', 'string', 'max:9'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'string', 'max:1'],
        ]);

        $patient = Patient::create($validated);

        return response()->json($patient, 201);
    }

    public function show(Patient $patient)
    {
        Gate::authorize('view', $patient);
        return response()->json($patient);
    }

    public function update(Request $request, Patient $patient)
    {
        Gate::authorize('update', $patient);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'lastname' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:100', 'unique:patients,email,' . $patient->id],
            'phone' => ['nullable', 'string', 'max:9'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['sometimes', 'string', 'max:1'],
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        Gate::authorize('delete', $patient);
        $patient->delete();

        return response()->json([
            'message' => 'Paciente eliminado correctamente'
        ]);
    }
}
