@props([
    'namePrefix',
    'savedValue' => null,
    'metricsKeys' => [],
    'withStatus' => true,
    'allowAddRows' => true,
])

@php
    $metricsKeys = is_array($metricsKeys) && $metricsKeys !== [] ? $metricsKeys : ['Hearing level'];
    $aud = \App\Support\ClinicalCompositeFields::resolveAudForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue,
        $metricsKeys,
        $withStatus
    );
    $statusOptions = \App\Support\ClinicalCompositeFields::audStatusOptions();
    $metrics = $aud['metrics'];
    $status = $aud['status'];
@endphp

<div class="clinical-composite clinical-composite--aud" data-clinical-aud-root data-name-prefix="{{ $namePrefix }}">
    <div class="mb-3">
        @if($allowAddRows)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-muted">{{ __('workflow.fields.clinical_aud_metrics') }}</span>
                <button type="button" class="btn btn-outline-primary btn-sm" data-add-aud-metric>
                    <i class="ti ti-plus me-1"></i>{{ __('workflow.fields.clinical_aud_add_metric') }}
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0 clinical-kv-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 42%;">{{ __('workflow.fields.clinical_metric_key') }}</th>
                        <th>{{ __('workflow.fields.clinical_metric_value') }}</th>
                        @if($allowAddRows)
                            <th style="width: 44px;"></th>
                        @endif
                    </tr>
                </thead>
                <tbody data-aud-metrics-body>
                    @foreach($metrics as $index => $row)
                        <tr data-aud-metric-row>
                            <td>
                                <input type="text"
                                       name="{{ $namePrefix }}[metrics][{{ $index }}][key]"
                                       data-aud-key
                                       class="form-control form-control-sm"
                                       value="{{ $row['key'] ?? '' }}"
                                       @if(!$allowAddRows) readonly @endif
                                       placeholder="{{ __('workflow.fields.clinical_metric_key') }}">
                            </td>
                            <td>
                                <input type="text"
                                       name="{{ $namePrefix }}[metrics][{{ $index }}][value]"
                                       data-aud-value
                                       class="form-control form-control-sm"
                                       value="{{ $row['value'] ?? '' }}"
                                       placeholder="{{ __('workflow.fields.clinical_metric_value') }}">
                            </td>
                            @if($allowAddRows)
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-aud-metric aria-label="{{ __('common.delete') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($withStatus)
        <div>
            <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.clinical_aud_status') }}</label>
            <select name="{{ $namePrefix }}[status]" class="form-select form-select-sm">
                <option value="">— {{ __('common.select') }} —</option>
                @foreach($statusOptions as $code => $label)
                    <option value="{{ $code }}" @selected((string) $status === (string) $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
</div>
