@props([
    'campaign' => null,
    'statuses' => [],
])

@php
    $selectedCountryId = old('country_id', $campaign?->country_id);
    $selectedCityId = old('city_id', $campaign?->city_id);
    $selectedCountry = $selectedCountryId
        ? (\App\Models\Country::query()->find($selectedCountryId) ?? $campaign?->country)
        : null;
    $selectedCity = $selectedCityId
        ? (\App\Models\City::query()->find($selectedCityId) ?? $campaign?->city)
        : null;
    $selectedSpecialtyId = old('specialty_id', $campaign?->specialty_id);
    $selectedSpecialty = $selectedSpecialtyId
        ? (\App\Models\Specialty::query()->find($selectedSpecialtyId) ?? $campaign?->specialty)
        : null;
@endphp

<div class="row g-3">
    <div class="col-12">
        <x-card :title="__('campaigns.sections.basic')">
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('campaigns.fields.name')" name="name" :value="old('name', $campaign?->name)" :placeholder="__('campaigns.placeholders.name')" required />
                </div>
                <div class="col-md-6 ps-md-2">
                    @if($campaign?->code)
                        <label class="form-group-admin__label">{{ __('campaigns.fields.code') }}</label>
                        <div class="form-control form-control-sm bg-light">
                            <code>{{ $campaign->code }}</code>
                        </div>
                        <div class="form-text">{{ __('campaigns.hints.code_auto') }}</div>
                    @else
                        <label class="form-group-admin__label">{{ __('campaigns.fields.code') }}</label>
                        <div class="form-control form-control-sm bg-light text-muted">{{ __('campaigns.hints.code_generated_on_save') }}</div>
                    @endif
                </div>
            </div>
            <div class="row g-0">
                <div class="col-md-12">
                    <x-form-input :label="__('campaigns.fields.target_group')" name="target_group" :value="old('target_group', $campaign?->target_group)" :placeholder="__('campaigns.placeholders.target_group')" required />
                </div>
            </div>
            <x-form-input :label="__('campaigns.fields.objective')" name="objective" type="textarea" :value="old('objective', $campaign?->objective)" :placeholder="__('campaigns.placeholders.objective')" required />
        </x-card>
    </div>

    <div class="col-12">
        <x-card :title="__('campaigns.sections.details')">
            <x-location-picker
                :selected-country-id="$selectedCountryId"
                :selected-city-id="$selectedCityId"
                :selected-country-name="$selectedCountry?->localizedName()"
                :selected-city-name="$selectedCity?->localizedName()"
            />

            <div class="row g-0 mt-2">
                <div class="col-md-6 pe-md-2">
                    <x-specialty-picker
                        :selected-specialty-id="$selectedSpecialtyId"
                        :selected-specialty-name="$selectedSpecialty?->name"
                    />
                </div>
            </div>

            <x-form-input :label="__('campaigns.fields.description')" name="description" type="textarea" :value="old('description', $campaign?->description)" :placeholder="__('campaigns.placeholders.description')" />
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('campaigns.sections.schedule')">
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('campaigns.fields.start_date')" name="start_date" type="date" :value="old('start_date', $campaign?->start_date?->format('Y-m-d'))" required />
                </div>
                <div class="col-md-6 ps-md-2">
                    <x-form-input :label="__('campaigns.fields.end_date')" name="end_date" type="date" :value="old('end_date', $campaign?->end_date?->format('Y-m-d'))" required />
                </div>
            </div>
            @if($campaign?->start_date && $campaign?->end_date)
                <p class="text-muted small mb-0 mt-2">
                    {{ __('campaigns.fields.campaign_days_count') }}:
                    <strong>{{ $campaign->campaignDaysCount() }}</strong>
                    <span class="d-block">{{ __('campaigns.hints.campaign_days_auto') }}</span>
                </p>
            @else
                <p class="text-muted small mb-0 mt-2">{{ __('campaigns.hints.campaign_days_auto') }}</p>
            @endif
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('campaigns.sections.expected')">
            <x-form-input :label="__('campaigns.fields.expected_patients')" name="expected_patients" type="number" :value="old('expected_patients', $campaign?->expected_patients ?? 0)" min="0" required />
            @if($campaign)
                <x-form-input :label="__('campaigns.fields.status')" name="campaign_status_id" type="select" required>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected((string) old('campaign_status_id', $campaign?->campaign_status_id) === (string) $status->id)>{{ $status->label() }}</option>
                    @endforeach
                </x-form-input>
            @endif
        </x-card>
    </div>
</div>
