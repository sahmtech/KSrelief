@props(['patientEligibilityStatus' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $patientEligibilityStatus?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $patientEligibilityStatus?->code)" required />
    </div>
</div>
<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.color')" name="color" type="color" :value="old('color', $patientEligibilityStatus?->color ?? '#64748B')" class="form-control form-control-color" />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.sort_order')" name="sort_order" type="number" :value="old('sort_order', $patientEligibilityStatus?->sort_order ?? 0)" min="0" />
    </div>
</div>
<x-settings.status-select :value="old('status', $patientEligibilityStatus?->status?->value ?? 'active')" />
