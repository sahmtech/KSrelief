@props([
    'prefix' => '',
    'selectedSpecialtyId' => null,
    'selectedSpecialtyName' => null,
    'label' => null,
    'required' => true,
    'canAddSpecialty' => null,
])

@php
    $selectId = $prefix.'specialty_id';
    $wrapperId = $prefix.'specialty_picker';
    $canAddSpecialty = $canAddSpecialty ?? (
        auth()->user()?->can('campaign.create')
        || auth()->user()?->can('campaign.update')
        || auth()->user()?->can('settings.update')
    );
@endphp

<div
    class="specialty-picker"
    id="{{ $wrapperId }}"
    data-specialty-picker
    data-selected-specialty="{{ $selectedSpecialtyId }}"
    data-selected-specialty-name="{{ $selectedSpecialtyName }}"
    data-required="{{ $required ? '1' : '0' }}"
    data-can-add-specialty="{{ $canAddSpecialty ? '1' : '0' }}"
    data-placeholder="{{ __('campaigns.placeholders.select_specialty') }}"
    data-i18n-no-results="{{ __('specialties.messages.no_results') }}"
    data-i18n-searching="{{ __('specialties.messages.searching') }}"
    data-i18n-input-too-short="{{ __('specialties.messages.type_to_search') }}"
    data-url-specialties="{{ route('specialties.index') }}"
>
    <div class="form-group-admin">
        <label for="{{ $selectId }}" class="form-group-admin__label">
            {{ $label ?? __('campaigns.fields.specialty') }}
            @if($required)<span class="required">*</span>@endif
        </label>
        <select
            name="specialty_id"
            id="{{ $selectId }}"
            class="form-group-admin__input @error('specialty_id') is-invalid @enderror"
            data-specialty-select
            {{ $required ? 'required' : '' }}
        >
            <option value="">{{ __('campaigns.placeholders.select_specialty') }}</option>
        </select>
        @error('specialty_id')
            <div class="form-group-admin__error">{{ $message }}</div>
        @enderror
        @if($canAddSpecialty)
            <div class="mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-specialty-add>
                    <i class="ti ti-plus me-1"></i> {{ __('specialties.add') }}
                </button>
                <div class="form-group-admin__hint mt-1">{{ __('specialties.messages.add_hint') }}</div>
            </div>
        @endif
    </div>

    @if($canAddSpecialty)
        <div class="modal fade" tabindex="-1" data-specialty-modal>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('specialties.add_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-admin mb-0">
                            <label class="form-group-admin__label">{{ __('specialties.fields.name') }}</label>
                            <input type="text" class="form-group-admin__input" data-specialty-new-name placeholder="{{ __('specialties.placeholders.name') }}">
                        </div>
                        <div class="text-danger mt-2 d-none" data-specialty-error></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn btn-primary" data-specialty-save>{{ __('common.create') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
