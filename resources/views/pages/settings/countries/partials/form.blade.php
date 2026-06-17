@props(['country' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $country?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.name_ar')" name="name_ar" :value="old('name_ar', $country?->name_ar)" />
    </div>
</div>
<div class="row g-0">
    <div class="col-md-4 pe-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $country?->code)" required />
    </div>
    <div class="col-md-4 px-md-1">
        <x-form-input :label="__('settings.fields.iso2')" name="iso2" :value="old('iso2', $country?->iso2)" maxlength="2" />
    </div>
    <div class="col-md-4 ps-md-2">
        <x-form-input :label="__('settings.fields.iso3')" name="iso3" :value="old('iso3', $country?->iso3)" maxlength="3" />
    </div>
</div>
<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.phone_code')" name="phone_code" :value="old('phone_code', $country?->phone_code)" />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-settings.status-select :value="old('status', $country?->status?->value ?? 'active')" />
    </div>
</div>
