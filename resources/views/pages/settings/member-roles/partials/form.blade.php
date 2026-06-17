@props(['memberRole' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $memberRole?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $memberRole?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.description')" name="description" type="textarea" :value="old('description', $memberRole?->description)" />
<x-settings.status-select :value="old('status', $memberRole?->status?->value ?? 'active')" />
