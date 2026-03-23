<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'blood_type' => $this->blood_type,
            'allergies' => $this->allergies,
            'chronic_diseases' => $this->chronic_diseases,
            'cronic_diseases' => $this->chronic_diseases,
            'medications' => $this->medications,
            'family_history' => $this->family_history,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
