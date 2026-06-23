@props(['option' => null])

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $option?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $option?->code)" required />
    </div>
</div>
<x-form-input :label="__('settings.fields.sort_order')" name="sort_order" type="number" :value="old('sort_order', $option?->sort_order ?? 0)" min="0" />
<x-settings.status-select :value="old('status', $option?->status?->value ?? 'active')" />
