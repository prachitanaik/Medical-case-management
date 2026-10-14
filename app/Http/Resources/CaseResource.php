<?php

namespace App\Http\Resources;

use App\Models\MedicalCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MedicalCase
 */
class CaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'patient_reference' => $this->patient_reference,
            'surgeon_name' => $this->surgeon_name,
            'implant_type' => $this->implant_type,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'surgery_date' => $this->surgery_date?->format('Y-m-d'),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
