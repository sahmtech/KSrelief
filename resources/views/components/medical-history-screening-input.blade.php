@props([
    'namePrefix',
    'savedValue' => null,
    'lists' => [],
])

@php
    $data = \App\Support\ScreeningFieldSupport::resolveMedicalHistoryForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue
    );
@endphp

<div class="clinical-medical-history-screening row g-3">
    @foreach($lists as $listKey => $listDef)
        @php
            $listLabel = $listDef['label'] ?? $listKey;
            $listOptions = $listDef['options'] ?? [];
            $current = $data[$listKey] ?? '';
        @endphp
        <div class="col-md-6">
            <label class="form-label fw-semibold small mb-1">{{ $listLabel }}</label>
            <select name="{{ $namePrefix }}[{{ $listKey }}]" class="form-select form-select-sm">
                <option value="">— {{ __('common.select') }} —</option>
                @foreach($listOptions as $code => $label)
                    <option value="{{ $code }}" @selected((string) $current === (string) $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
</div>
