@props([
    'user' => null,
    'inputId' => 'avatarInput',
    'previewId' => 'avatarPreview',
])

@php
    $hasAvatar = $user?->hasAvatar() ?? false;
@endphp

<div class="avatar-upload" data-avatar-upload data-avatar-initials="{{ $user?->initials() ?? '?' }}">
    <div class="avatar-upload__preview" id="{{ $previewId }}" data-avatar-preview>
        <x-user-avatar :user="$user" size="lg" />
    </div>

    <div class="avatar-upload__controls">
        <label class="form-group-admin__label">{{ __('users.fields.avatar') }}</label>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <label class="btn btn-outline-primary btn-sm mb-0" for="{{ $inputId }}">
                <i class="ti ti-upload me-1"></i>
                {{ $hasAvatar ? __('users.avatar.change') : __('users.avatar.choose') }}
            </label>
            <input
                type="file"
                name="avatar"
                id="{{ $inputId }}"
                class="d-none"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                data-avatar-input
            >
        </div>

        @if($hasAvatar)
            <div class="form-check mt-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remove_avatar"
                    value="1"
                    id="removeAvatar{{ $inputId }}"
                    data-avatar-remove
                    @checked(old('remove_avatar'))
                >
                <label class="form-check-label" for="removeAvatar{{ $inputId }}">
                    {{ __('users.avatar.remove') }}
                </label>
            </div>
        @endif

        <div class="form-group-admin__hint mt-2">{{ __('users.avatar.hint') }}</div>

        @error('avatar')
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
