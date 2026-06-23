<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UploadPatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');

        return $patient instanceof Patient
            && ($this->user()?->can('uploadAttachment', $patient) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $fileRules = [
            'file',
            'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,mp4,webm,mov',
            'max:51200',
        ];

        return [
            'file' => ['nullable', ...$fileRules],
            'files' => ['nullable', 'array', 'max:10'],
            'files.*' => $fileRules,
            'notes' => ['nullable', 'string', 'max:2000'],
            'return_to' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->file('file') && empty($this->file('files'))) {
                $validator->errors()->add('files', __('patients.messages.attachment_required'));
            }
        });
    }
}
