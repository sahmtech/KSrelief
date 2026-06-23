@php
    $previewable = $patient->attachments->filter(fn ($a) => $a->isPreviewable());
    $documents = $patient->attachments->reject(fn ($a) => $a->isPreviewable());
@endphp

<x-card :title="__('patients.brief.media')" class="mb-4">
    @can('uploadAttachment', $patient)
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#briefUploadMedia">
                <i class="ti ti-upload me-1"></i>{{ __('patients.brief.upload_media') }}
            </button>
        </x-slot:actions>

        <div class="collapse @if($errors->has('files') || $errors->has('file')) show @endif" id="briefUploadMedia">
            <div class="border-bottom pb-3 mb-3">
                <form method="POST" action="{{ route('patients.attachments.store', $patient) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('patients.brief', $patient) }}">
                    <div class="col-md-6">
                        <label class="form-group-admin__label">{{ __('patients.brief.upload_files_label') }}</label>
                        <input
                            type="file"
                            name="files[]"
                            class="form-group-admin__input @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror"
                            accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx"
                            multiple
                            required
                        >
                        <div class="form-text">{{ __('patients.brief.upload_files_hint') }}</div>
                        @error('files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-group-admin__label">{{ __('patients.fields.attachment_notes') }}</label>
                        <input type="text" name="notes" class="form-group-admin__input" value="{{ old('notes') }}" maxlength="500" placeholder="{{ __('patients.brief.upload_notes_placeholder') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="ti ti-upload me-1"></i>{{ __('patients.actions.upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @if($patient->attachments->isEmpty())
        <p class="text-muted mb-0 small">{{ __('patients.brief.no_media') }}</p>
    @else
        @if($previewable->isNotEmpty())
            <h6 class="fw-semibold small text-uppercase text-muted mb-3">{{ __('patients.brief.photos_videos') }}</h6>
            <div class="patient-media-grid mb-4">
                @foreach($previewable as $attachment)
                    <div class="patient-media-item">
                        @if($attachment->isImage())
                            <button
                                type="button"
                                class="patient-media-item__preview"
                                data-bs-toggle="modal"
                                data-bs-target="#briefMediaModal"
                                data-media-type="image"
                                data-media-src="{{ route('patients.attachments.preview', [$patient, $attachment]) }}"
                                data-media-title="{{ $attachment->original_name }}"
                            >
                                <img src="{{ route('patients.attachments.preview', [$patient, $attachment]) }}" alt="{{ $attachment->original_name }}" loading="lazy">
                            </button>
                        @else
                            <div class="patient-media-item__video">
                                <video controls preload="metadata" playsinline>
                                    <source src="{{ route('patients.attachments.preview', [$patient, $attachment]) }}" type="{{ $attachment->file_type }}">
                                </video>
                            </div>
                        @endif
                        <div class="patient-media-item__meta">
                            <div class="patient-media-item__name text-truncate" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</div>
                            <div class="patient-media-item__info">
                                <span>{{ $attachment->humanFileSize() }}</span>
                                @if($attachment->notes)
                                    <span class="text-muted">· {{ $attachment->notes }}</span>
                                @endif
                            </div>
                            <div class="patient-media-item__actions">
                                <a href="{{ route('patients.attachments.download', [$patient, $attachment]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('patients.actions.download') }}">
                                    <i class="ti ti-download"></i>
                                </a>
                                @can('deleteAttachment', $patient)
                                    <form method="POST" action="{{ route('patients.attachments.destroy', [$patient, $attachment]) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return_to" value="{{ route('patients.brief', $patient) }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('patients.messages.confirm_remove_attachment') }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($documents->isNotEmpty())
            <h6 class="fw-semibold small text-uppercase text-muted mb-3">{{ __('patients.brief.documents') }}</h6>
            <div class="list-group list-group-flush border rounded">
                @foreach($documents as $attachment)
                    <div class="list-group-item d-flex align-items-center gap-3 py-2">
                        <div class="patient-media-doc-icon">
                            <i class="ti {{ $attachment->iconClass() }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-medium text-truncate">{{ $attachment->original_name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                {{ $attachment->humanFileSize() }}
                                @if($attachment->notes) · {{ $attachment->notes }} @endif
                                · {{ $attachment->created_at->format('Y-m-d') }}
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <a href="{{ route('patients.attachments.download', [$patient, $attachment]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-download"></i>
                            </a>
                            @can('deleteAttachment', $patient)
                                <form method="POST" action="{{ route('patients.attachments.destroy', [$patient, $attachment]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ route('patients.brief', $patient) }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('patients.messages.confirm_remove_attachment') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</x-card>

<div class="modal fade" id="briefMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="briefMediaModalTitle"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="briefMediaModalImage" src="" alt="" class="img-fluid d-none" style="max-height: 75vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('briefMediaModal')?.addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const title = trigger.getAttribute('data-media-title') || '';
    const src = trigger.getAttribute('data-media-src') || '';
    const img = document.getElementById('briefMediaModalImage');

    document.getElementById('briefMediaModalTitle').textContent = title;
    if (img) {
        img.src = src;
        img.alt = title;
        img.classList.remove('d-none');
    }
});
</script>
@endpush

@push('styles')
<style>
    .patient-media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem}
    .patient-media-item{border:1px solid rgba(0,0,0,.08);border-radius:.75rem;overflow:hidden;background:#fff}
    .patient-media-item__preview{display:block;width:100%;padding:0;border:0;background:#f8fafc;cursor:zoom-in}
    .patient-media-item__preview img{width:100%;height:140px;object-fit:cover;display:block}
    .patient-media-item__video video{width:100%;height:140px;object-fit:cover;display:block;background:#000}
    .patient-media-item__meta{padding:.625rem .75rem}
    .patient-media-item__name{font-size:.8125rem;font-weight:600}
    .patient-media-item__info{font-size:.7rem;color:#64748b;margin:.2rem 0 .5rem}
    .patient-media-item__actions{display:flex;gap:.35rem}
    .patient-media-doc-icon{width:36px;height:36px;border-radius:.5rem;background:rgba(15,118,110,.08);color:#0f766e;display:flex;align-items:center;justify-content:center;flex-shrink:0}
</style>
@endpush
