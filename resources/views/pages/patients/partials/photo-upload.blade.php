@props([
    'patient' => null,
    'inputId' => 'patientPhotoInput',
    'previewId' => 'patientPhotoPreview',
])

@php
    $hasPhoto = $patient?->hasPhoto() ?? false;
@endphp

<div class="avatar-upload" data-avatar-upload data-avatar-initials="{{ $patient?->initials() ?? '?' }}">
    <div class="avatar-upload__preview" id="{{ $previewId }}" data-avatar-preview>
        <x-patient-avatar :patient="$patient" size="lg" />
    </div>

    <div class="avatar-upload__controls">
        <label class="form-group-admin__label">{{ __('patients.fields.photo') }}</label>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <label class="btn btn-outline-primary btn-sm mb-0" for="{{ $inputId }}">
                <i class="ti ti-upload me-1"></i>
                {{ $hasPhoto ? __('patients.photo.change') : __('patients.photo.choose') }}
            </label>
            <input
                type="file"
                name="photo"
                id="{{ $inputId }}"
                class="d-none"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                data-avatar-input
            >
        </div>

        @if($hasPhoto)
            <div class="form-check mt-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remove_photo"
                    value="1"
                    id="removePhoto{{ $inputId }}"
                    data-avatar-remove
                    @checked(old('remove_photo'))
                >
                <label class="form-check-label" for="removePhoto{{ $inputId }}">
                    {{ __('patients.photo.remove') }}
                </label>
            </div>
        @endif

        <div class="form-group-admin__hint mt-2">{{ __('patients.photo.hint') }}</div>

        @error('photo')
            <div class="form-group-admin__error mt-2">{{ $message }}</div>
        @enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-avatar-upload]').forEach((wrapper) => {
                const input = wrapper.querySelector('[data-avatar-input]');
                const preview = wrapper.querySelector('[data-avatar-preview]');
                const removeCheckbox = wrapper.querySelector('[data-avatar-remove]');

                if (!input || !preview) {
                    return;
                }

                const defaultPreview = preview.innerHTML;

                input.addEventListener('change', () => {
                    const file = input.files?.[0];

                    if (!file) {
                        preview.innerHTML = defaultPreview;
                        return;
                    }

                    if (removeCheckbox) {
                        removeCheckbox.checked = false;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        preview.innerHTML = `
                            <div class="user-avatar user-avatar--lg user-avatar--image">
                                <img src="${event.target.result}" alt="" class="user-avatar__img">
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                });

                if (removeCheckbox) {
                    removeCheckbox.addEventListener('change', () => {
                        if (removeCheckbox.checked) {
                            input.value = '';
                            preview.innerHTML = `
                                <div class="user-avatar user-avatar--lg">
                                    <span class="user-avatar__initials">${wrapper.dataset.avatarInitials}</span>
                                </div>
                            `;
                        } else {
                            preview.innerHTML = defaultPreview;
                        }
                    });
                }
            });
        </script>
    @endpush
@endonce
