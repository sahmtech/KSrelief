@props([
    'namePrefix',
    'savedValue' => null,
    'defaultKeys' => [],
    'tableTitle' => null,
    'allowAddRows' => true,
    'required' => false,
])

@php
    $defaultKeys = is_array($defaultKeys) && $defaultKeys !== [] ? $defaultKeys : [''];
    $savedMetrics = is_array($savedValue) ? ($savedValue['metrics'] ?? $savedValue) : [];
    $table = \App\Support\ClinicalCompositeFields::resolveAudForForm(
        ['metrics' => $savedMetrics],
        $defaultKeys,
        false
    );
    $metrics = $table['metrics'];
@endphp

<div class="clinical-kv-table-block mb-3" data-clinical-aud-root data-name-prefix="{{ $namePrefix }}">
    @if(filled($tableTitle))
        <div class="small fw-semibold text-muted mb-2">
            {{ $tableTitle }}
            @if($required)<span class="text-danger">*</span>@endif
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

    @if($allowAddRows)
        <div class="clinical-kv-table-add-row">
            <button type="button" class="btn btn-outline-primary btn-sm" data-add-aud-metric>
                <i class="ti ti-plus me-1"></i>{{ __('workflow.fields.clinical_aud_add_metric') }}
            </button>
        </div>
    @endif
</div>
