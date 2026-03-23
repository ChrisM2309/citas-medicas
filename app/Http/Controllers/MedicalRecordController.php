<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalRecords\StoreMedicalRecordRequest;
use App\Http\Requests\MedicalRecords\UpdateMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\Patient;

class MedicalRecordController extends Controller
{
    public function show(Patient $patient)
    {
        return response()->json($patient->medicalRecord);
    }

    public function store(StoreMedicalRecordRequest $request, Patient $patient)
    {
        $record = MedicalRecord::create([
            'patient_id' => $patient->id,
            ...$this->normalizedPayload($request->validated()),
        ]);

        return response()->json($record, 201);
    }

    public function update(UpdateMedicalRecordRequest $request, Patient $patient)
    {
        $record = $patient->medicalRecord;
        $record->update($this->normalizedPayload($request->validated()));

        return response()->json($record);
    }

    private function normalizedPayload(array $validated): array
    {
        $validated['chronic_diseases'] = $validated['chronic_diseases'] ?? $validated['cronic_diseases'] ?? null;
        unset($validated['cronic_diseases']);

        return $validated;
    }
}
