@props(['attendanceStatus' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $attendanceStatus?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $attendanceStatus?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.color')" name="color" type="color" :value="old('color', $attendanceStatus?->color ?? '#22C55E')" class="form-control form-control-color" />
<x-settings.status-select :value="old('status', $attendanceStatus?->status?->value ?? 'active')" />
