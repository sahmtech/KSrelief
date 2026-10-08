<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithPatientAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\UploadPatientAttachmentRequest;
use App\Http\Resources\PatientAttachmentResource;
use App\Models\Patient;
use App\Models\PatientAttachment;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientAttachmentController extends Controller
{
    use InteractsWithPatientAccess;

    public function __construct(private readonly PatientService $patientService) {}

    public function store(UploadPatientAttachmentRequest $request, Patient $patient): JsonResponse
    {
        $this->assertPatientAccessible($patient);

        $uploaded = [];

        $files = collect($request->file('files', []))
            ->when($request->file('file'), fn ($collection) => $collection->prepend($request->file('file')))
            ->filter()
            ->values();

        foreach ($files as $file) {
            $uploaded[] = $this->patientService->uploadAttachment(
                $patient,
                $file,
                $request->user(),
                $request->validated('notes')
            );
        }

        return response()->json([
            'message' => __('patients.messages.attachment_uploaded'),
            'data' => PatientAttachmentResource::collection(
                collect($uploaded)->each->load('uploader')
            ),
        ], 201);
    }

    public function preview(Patient $patient, PatientAttachment $attachment): BinaryFileResponse
    {
        $this->authorize('view', $patient);
        $this->assertPatientAccessible($patient);

        abort_unless($attachment->patient_id === $patient->id, 404);
        abort_unless($attachment->isPreviewable(), 404);
        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404);

        return response()->file(
            Storage::disk('local')->path($attachment->storage_path),
            [
                'Content-Type' => $attachment->file_type,
                'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
            ]
        );
    }

    public function download(Patient $patient, PatientAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $patient);
        $this->assertPatientAccessible($patient);

        abort_unless($attachment->patient_id === $patient->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404);

        return Storage::disk('local')->download(
            $attachment->storage_path,
            $attachment->original_name
        );
    }

    public function destroy(Patient $patient, PatientAttachment $attachment): JsonResponse
    {
        $this->authorize('deleteAttachment', $patient);
        $this->assertPatientAccessible($patient);

        abort_unless($attachment->patient_id === $patient->id, 404);

        $this->patientService->removeAttachment($attachment);

        return response()->json([
            'message' => __('patients.messages.attachment_deleted'),
        ]);
    }
}
