<?php

namespace App\Http\Resources;

use App\Models\PatientAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PatientAttachment */
class PatientAttachmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $patientId = $this->patient_id;

        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'human_file_size' => $this->humanFileSize(),
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_previewable' => $this->isPreviewable(),
            'icon' => $this->iconClass(),
            'notes' => $this->notes,
            'uploaded_by' => $this->whenLoaded('uploader', fn () => [
                'id' => $this->uploader->id,
                'name' => $this->uploader->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'preview_url' => $this->isPreviewable()
                ? route('api.v1.patients.attachments.preview', [$patientId, $this->id])
                : null,
            'download_url' => route('api.v1.patients.attachments.download', [$patientId, $this->id]),
        ];
    }
}
