<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\UploadPatientAttachmentRequest;
use App\Models\Patient;
use App\Models\PatientAttachment;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PatientAttachmentController extends Controller
{
    public function __construct(
        private readonly PatientService $patientService
    ) {}

    public function store(UploadPatientAttachmentRequest $request, Patient $patient): RedirectResponse
    {
        $this->patientService->uploadAttachment(
            $patient,
            $request->file('file'),
            $request->user(),
            $request->validated('notes')
        );

        return back()->with('success', __('patients.messages.attachment_uploaded'));
    }

    public function download(Patient $patient, PatientAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $patient);

        abort_unless($attachment->patient_id === $patient->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404);

        return Storage::disk('local')->download(
            $attachment->storage_path,
            $attachment->original_name
        );
    }

    public function destroy(Patient $patient, PatientAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $patient);

        abort_unless($attachment->patient_id === $patient->id, 404);

        $this->patientService->removeAttachment($attachment);

        return back()->with('success', __('patients.messages.attachment_deleted'));
    }
}
