@props(['city' => null, 'countries' => []])

<x-form-input :label="__('settings.fields.country')" name="country_id" type="select" :value="old('country_id', $city?->country_id)" required>
    <option value="">{{ __('settings.fields.country') }}</option>
    @foreach($countries as $country)
        <option value="{{ $country->id }}" @selected((string) old('country_id', $city?->country_id) === (string) $country->id)>
            {{ $country->localizedName() }}
        </option>
    @endforeach
</x-form-input>
<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $city?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.name_ar')" name="name_ar" :value="old('name_ar', $city?->name_ar)" />
    </div>
</div>
<x-settings.status-select :value="old('status', $city?->status?->value ?? 'active')" />
