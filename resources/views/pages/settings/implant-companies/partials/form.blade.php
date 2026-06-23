@props(['company' => null])

@php
    $electrodeRows = old('electrode_types');
    if ($electrodeRows === null && $company) {
        $electrodeRows = $company->electrodeTypes->map(fn ($type) => ['id' => $type->id, 'name' => $type->name])->all();
    }
    $electrodeRows = is_array($electrodeRows) ? $electrodeRows : [];
@endphp

<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $company?->name)" required />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.code')" name="code" :value="old('code', $company?->code)" required />
    </div>
</div>
<div class="row g-0">
    <div class="col-md-6 pe-md-2">
        <x-form-input :label="__('settings.fields.color')" name="color" type="color" :value="old('color', $company?->color ?? '#DC2626')" class="form-control form-control-color" />
    </div>
    <div class="col-md-6 ps-md-2">
        <x-form-input :label="__('settings.fields.sort_order')" name="sort_order" type="number" :value="old('sort_order', $company?->sort_order ?? 0)" min="0" />
    </div>
</div>
<x-settings.status-select :value="old('status', $company?->status?->value ?? 'active')" />

<div class="mt-4 pt-3 border-top">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-semibold">{{ __('settings.implant_companies.electrode_types') }}</h6>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addElectrodeRow">
            <i class="ti ti-plus me-1"></i> {{ __('settings.implant_companies.add_electrode') }}
        </button>
    </div>
    <p class="text-muted small mb-3">{{ __('settings.implant_companies.electrode_types_hint') }}</p>

    <div id="electrodeTypesList" class="d-flex flex-column gap-2">
        @foreach($electrodeRows as $index => $row)
            <div class="electrode-row d-flex gap-2 align-items-center">
                @if(!empty($row['id']))
                    <input type="hidden" name="electrode_types[{{ $index }}][id]" value="{{ $row['id'] }}">
                @endif
                <input type="text" name="electrode_types[{{ $index }}][name]" class="form-control form-control-sm"
                       value="{{ $row['name'] ?? '' }}" placeholder="{{ __('settings.implant_companies.electrode_name_placeholder') }}">
                <button type="button" class="btn btn-outline-danger btn-sm remove-electrode-row" aria-label="{{ __('common.delete') }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(function () {
    const list = document.getElementById('electrodeTypesList');
    const addBtn = document.getElementById('addElectrodeRow');
    if (!list || !addBtn) return;

    let rowIndex = list.querySelectorAll('.electrode-row').length;

    function bindRemove(row) {
        row.querySelector('.remove-electrode-row')?.addEventListener('click', () => row.remove());
    }

    list.querySelectorAll('.electrode-row').forEach(bindRemove);

    addBtn.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'electrode-row d-flex gap-2 align-items-center';
        row.innerHTML = `
            <input type="text" name="electrode_types[${rowIndex}][name]" class="form-control form-control-sm"
                   placeholder="{{ __('settings.implant_companies.electrode_name_placeholder') }}">
            <button type="button" class="btn btn-outline-danger btn-sm remove-electrode-row" aria-label="{{ __('common.delete') }}">
                <i class="ti ti-trash"></i>
            </button>
        `;
        bindRemove(row);
        list.appendChild(row);
        rowIndex++;
    });
})();
</script>
@endpush
