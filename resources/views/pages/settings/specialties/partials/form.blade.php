@props(['specialty' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $specialty?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $specialty?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.description')" name="description" type="textarea" :value="old('description', $specialty?->description)" />
<x-settings.status-select :value="old('status', $specialty?->status?->value ?? 'active')" />
