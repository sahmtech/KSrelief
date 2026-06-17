@props([
    'name',
    'label',
    'selectedId' => null,
    'selectedLabel' => null,
    'required' => true,
    'locations' => collect(),
])

@php
    $selectId = str_replace(['[', ']'], '_', $name);
    $resolvedId = old($name, $selectedId);
    $resolvedLabel = $selectedLabel;

    if ($resolvedId && ! $resolvedLabel) {
        $match = $locations->firstWhere('id', (int) $resolvedId);
        $resolvedLabel = $match
            ? $match->name.' ('.__('settings.transportation_types.'.$match->type).')'
            : null;
    }

    $canAdd = auth()->user()?->can('transport_location.create')
        || auth()->user()?->can('transportation.create');

    $locationTypes = ['hotel', 'hospital', 'airport', 'other'];
@endphp

<div
    class="transportation-location-picker"
    data-transportation-location-picker
    data-selected-id="{{ $resolvedId }}"
    data-selected-label="{{ $resolvedLabel }}"
    data-can-add="{{ $canAdd ? '1' : '0' }}"
    data-placeholder="{{ __('transportation.locations.search_placeholder') }}"
    data-i18n-no-results="{{ __('transportation.locations.no_results') }}"
    data-i18n-searching="{{ __('transportation.locations.searching') }}"
    data-i18n-input-too-short="{{ __('transportation.locations.type_to_search') }}"
    data-i18n-required="{{ __('transportation.locations.validation_required') }}"
    data-url-search="{{ route('operations.transportation.locations.search') }}"
    data-url-store="{{ route('operations.transportation.locations.store') }}"
>
    <div class="form-group-admin">
        <label for="{{ $selectId }}" class="form-group-admin__label">
            {{ $label }}
            @if($required)<span class="required">*</span>@endif
        </label>
        <select
            name="{{ $name }}"
            id="{{ $selectId }}"
            class="form-group-admin__input @error($name) is-invalid @enderror"
            data-transportation-location-select
            {{ $required ? 'required' : '' }}
        >
            <option value="">{{ __('common.select') }}</option>
        </select>
        @error($name)
            <div class="form-group-admin__error">{{ $message }}</div>
        @enderror
        @if($canAdd)
            <div class="mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-transportation-location-add>
                    <i class="ti ti-plus me-1"></i> {{ __('transportation.locations.add') }}
                </button>
                <div class="form-group-admin__hint mt-1">{{ __('transportation.locations.add_hint') }}</div>
            </div>
        @endif
    </div>

    @if($canAdd)
        <div class="modal fade" tabindex="-1" data-transportation-location-modal>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('transportation.locations.add_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-admin">
                            <label class="form-group-admin__label">{{ __('settings.fields.name') }} <span class="required">*</span></label>
                            <input type="text" class="form-group-admin__input" data-transportation-location-name placeholder="{{ __('settings.fields.name') }}">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-group-admin__label">{{ __('settings.fields.type') }} <span class="required">*</span></label>
                            <select class="form-group-admin__input" data-transportation-location-type>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($locationTypes as $type)
                                    <option value="{{ $type }}">{{ __('settings.transportation_types.'.$type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group-admin mb-0">
                            <label class="form-group-admin__label">{{ __('settings.fields.description') }}</label>
                            <textarea class="form-group-admin__input" rows="2" data-transportation-location-description placeholder="{{ __('settings.fields.description') }}"></textarea>
                        </div>
                        <div class="text-danger mt-2 d-none" data-transportation-location-error></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn btn-primary" data-transportation-location-save>{{ __('common.create') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
