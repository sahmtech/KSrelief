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
        $files = collect($request->file('files', []))
            ->when($request->file('file'), fn ($collection) => $collection->prepend($request->file('file')))
            ->filter()
            ->values();

        foreach ($files as $file) {
            $this->patientService->uploadAttachment(
                $patient,
                $file,
                $request->user(),
                $request->validated('notes')
            );
        }

        $redirect = $request->validated('return_to') ?: url()->previous();

        return redirect()->to($redirect)
            ->with('success', __('patients.messages.attachment_uploaded'));
    }

    public function preview(Patient $patient, PatientAttachment $attachment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view', $patient);

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

        abort_unless($attachment->patient_id === $patient->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404);

        return Storage::disk('local')->download(
            $attachment->storage_path,
            $attachment->original_name
        );
    }

    public function destroy(Patient $patient, PatientAttachment $attachment): RedirectResponse
    {
        $this->authorize('deleteAttachment', $patient);

        abort_unless($attachment->patient_id === $patient->id, 404);

        $this->patientService->removeAttachment($attachment);

        $redirect = request()->input('return_to') ?: url()->previous();

        return redirect()->to($redirect)
            ->with('success', __('patients.messages.attachment_deleted'));
    }
}
