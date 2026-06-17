@props([
    'prefix' => '',
    'selectedCountryId' => null,
    'selectedCityId' => null,
    'selectedCountryName' => null,
    'selectedCityName' => null,
    'countryLabel' => null,
    'cityLabel' => null,
    'required' => true,
    'canAddCity' => null,
])

@php
    $countryId = $prefix.'country_id';
    $cityId = $prefix.'city_id';
    $wrapperId = $prefix.'location_picker';
    $canAddCity = $canAddCity ?? (
        auth()->user()?->can('campaign.create')
        || auth()->user()?->can('campaign.update')
        || auth()->user()?->can('settings.update')
    );
@endphp

<div
    class="location-picker"
    id="{{ $wrapperId }}"
    data-location-picker
    data-prefix="{{ $prefix }}"
    data-selected-country="{{ $selectedCountryId }}"
    data-selected-city="{{ $selectedCityId }}"
    data-selected-country-name="{{ $selectedCountryName }}"
    data-selected-city-name="{{ $selectedCityName }}"
    data-required="{{ $required ? '1' : '0' }}"
    data-can-add-city="{{ $canAddCity ? '1' : '0' }}"
    data-placeholder-country="{{ __('campaigns.placeholders.select_country') }}"
    data-placeholder-city="{{ __('campaigns.placeholders.select_city') }}"
    data-i18n-no-results="{{ __('locations.messages.no_results') }}"
    data-i18n-searching="{{ __('locations.messages.searching') }}"
    data-i18n-input-too-short="{{ __('locations.messages.type_to_search') }}"
    data-url-countries="{{ route('locations.countries') }}"
    data-url-cities-base="{{ url('/locations/countries') }}"
>
    <div class="row g-0">
        <div class="col-md-6 pe-md-2">
            <div class="form-group-admin">
                <label for="{{ $countryId }}" class="form-group-admin__label">
                    {{ $countryLabel ?? __('locations.fields.country') }}
                    @if($required)<span class="required">*</span>@endif
                </label>
                <select name="country_id" id="{{ $countryId }}" class="form-group-admin__input location-picker__select @error('country_id') is-invalid @enderror" data-location-country {{ $required ? 'required' : '' }}>
                    <option value="">{{ __('campaigns.placeholders.select_country') }}</option>
                </select>
                @error('country_id')
                    <div class="form-group-admin__error">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6 ps-md-2">
            <div class="form-group-admin">
                <label for="{{ $cityId }}" class="form-group-admin__label">
                    {{ $cityLabel ?? __('locations.fields.city') }}
                    @if($required)<span class="required">*</span>@endif
                </label>
                <select name="city_id" id="{{ $cityId }}" class="form-group-admin__input location-picker__select @error('city_id') is-invalid @enderror" data-location-city {{ $required ? 'required' : '' }} disabled>
                    <option value="">{{ __('campaigns.placeholders.select_city') }}</option>
                </select>
                @error('city_id')
                    <div class="form-group-admin__error">{{ $message }}</div>
                @enderror
                @if($canAddCity)
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-location-add-city disabled>
                            <i class="ti ti-plus me-1"></i> {{ __('locations.add_city') }}
                        </button>
                        <div class="form-group-admin__hint mt-1">{{ __('locations.messages.add_city_hint') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($canAddCity)
        <div class="modal fade" tabindex="-1" data-location-city-modal>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('locations.add_city_title') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group-admin">
                            <label class="form-group-admin__label">{{ __('locations.fields.city_name') }}</label>
                            <input type="text" class="form-group-admin__input" data-location-new-city-name placeholder="{{ __('locations.placeholders.city_name') }}">
                        </div>
                        <div class="form-group-admin mb-0">
                            <label class="form-group-admin__label">{{ __('locations.fields.city_name_ar') }}</label>
                            <input type="text" class="form-group-admin__input" data-location-new-city-name-ar placeholder="{{ __('locations.placeholders.city_name_ar') }}">
                        </div>
                        <div class="text-danger mt-2 d-none" data-location-city-error></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn btn-primary" data-location-save-city>{{ __('common.create') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
