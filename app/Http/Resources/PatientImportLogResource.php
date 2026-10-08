<?php

namespace App\Http\Resources;

use App\Models\PatientImportLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientImportLog */
class PatientImportLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'row_number' => $this->row_number,
            'patient_name' => $this->patient_name,
            'file_number' => $this->file_number,
            'date_of_birth' => $this->raw_data['date_of_birth'] ?? null,
            'gender' => $this->raw_data['gender'] ?? null,
            'height_cm' => $this->raw_data['height_cm'] ?? null,
            'weight_kg' => $this->raw_data['weight_kg'] ?? null,
            'contact_number' => $this->raw_data['contact_number'] ?? null,
            'is_valid' => $this->is_valid,
            'is_duplicate' => $this->is_duplicate,
            'is_importable' => $this->isImportable(),
            'validation_errors' => $this->validation_errors ?? [],
            'duplicate_reason' => $this->duplicate_reason,
            'patient_id' => $this->patient_id,
            'status' => $this->rowStatusLabel(),
            'status_code' => $this->resolveStatusCode(),
        ];
    }

    private function resolveStatusCode(): string
    {
        if ($this->is_duplicate) {
            return 'duplicate';
        }

        if ($this->patient_id !== null) {
            return 'imported';
        }

        return $this->is_valid ? 'valid' : 'invalid';
    }
}
