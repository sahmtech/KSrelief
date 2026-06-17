@props(['activityType' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $activityType?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $activityType?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.color')" name="color" type="color" :value="old('color', $activityType?->color ?? '#14B8A6')" class="form-control form-control-color" />
<x-form-input :label="__('settings.fields.description')" name="description" type="textarea" :value="old('description', $activityType?->description)" />
<x-settings.status-select :value="old('status', $activityType?->status?->value ?? 'active')" />
