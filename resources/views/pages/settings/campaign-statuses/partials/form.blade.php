@props(['campaignStatus' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $campaignStatus?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $campaignStatus?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.color')" name="color" type="color" :value="old('color', $campaignStatus?->color ?? '#0F766E')" class="form-control form-control-color" />
<div class="form-group-admin">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_default" name="is_default" value="1" @checked(old('is_default', $campaignStatus?->is_default ?? false))>
        <label class="form-check-label" for="is_default">{{ __('settings.fields.is_default') }}</label>
    </div>
    @error('is_default')<div class="form-group-admin__error">{{ $message }}</div>@enderror
</div>
<x-settings.status-select :value="old('status', $campaignStatus?->status?->value ?? 'active')" />
