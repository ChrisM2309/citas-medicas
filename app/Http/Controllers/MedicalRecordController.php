<?php

namespace App\Http\Controllers;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
        public function show(Patient $patient)
    {
        return response()->json($patient->medicalRecord);
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'blood_type' => ['nullable', 'string', 'max:3'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'cronic_diseases' => ['nullable', 'string', 'max:255'],
            'medications' => ['nullable', 'string', 'max:255'],
            'family_history' => ['nullable', 'string', 'max:255'],
        ]);

        $record = MedicalRecord::create([
            'patient_id' => $patient->id,
            ...$validated
        ]);

        return response()->json($record, 201);
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'blood_type' => ['nullable', 'string', 'max:3'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'cronic_diseases' => ['nullable', 'string', 'max:255'],
            'medications' => ['nullable', 'string', 'max:255'],
            'family_history' => ['nullable', 'string', 'max:255'],
        ]);

        $record = $patient->medicalRecord;
        $record->update($validated);

        return response()->json($record);
    }
}