<x-card :title="__('patients.sections.attachments')" :flush="true">
    @can('uploadAttachment', $patient)
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#uploadAttachmentForm">
                <i class="ti ti-upload me-1"></i> {{ __('patients.actions.upload') }}
            </button>
        </x-slot:actions>

        <div class="collapse border-bottom" id="uploadAttachmentForm">
            <div class="p-3">
                <form method="POST" action="{{ route('patients.attachments.store', $patient) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-group-admin__label">{{ __('patients.fields.attachment') }}</label>
                        <input type="file" name="file" class="form-group-admin__input" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-group-admin__label">{{ __('patients.fields.attachment_notes') }}</label>
                        <input type="text" name="notes" class="form-group-admin__input" value="{{ old('notes') }}" maxlength="500">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="ti ti-upload me-1"></i> {{ __('patients.actions.upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @if($patient->attachments->isEmpty())
        <div class="text-center text-muted py-4">{{ __('patients.messages.no_attachments') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('patients.fields.attachment') }}</th>
                        <th>{{ __('patients.fields.attachment_notes') }}</th>
                        <th>{{ __('patients.fields.created_by') }}</th>
                        <th>{{ __('patients.fields.created_at') }}</th>
                        <th class="text-end">{{ __('patients.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->attachments as $attachment)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $attachment->original_name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $attachment->humanFileSize() }} · {{ $attachment->file_type }}</div>
                            </td>
                            <td>{{ $attachment->notes ?? '—' }}</td>
                            <td>{{ $attachment->uploader?->name ?? '—' }}</td>
                            <td>{{ $attachment->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                @if($attachment->isPreviewable())
                                    <a href="{{ route('patients.attachments.preview', [$patient, $attachment]) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="{{ __('patients.actions.view') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                @endif
                                <a href="{{ route('patients.attachments.download', [$patient, $attachment]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('patients.actions.download') }}">
                                    <i class="ti ti-download"></i>
                                </a>
                                @can('deleteAttachment', $patient)
                                    <form method="POST" action="{{ route('patients.attachments.destroy', [$patient, $attachment]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('patients.messages.confirm_remove_attachment') }}" title="{{ __('patients.actions.remove_attachment') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
