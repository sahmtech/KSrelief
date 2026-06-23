@props([
    'value' => null,
    'fieldDefinition' => [],
])

@php
    $data = \App\Support\ClinicalCompositeFields::normalizeAud(
        $value,
        \App\Support\ClinicalCompositeFields::metricsKeysFromDefinition($fieldDefinition),
        (bool) ($fieldDefinition['with_status'] ?? true)
    );
    $statusOptions = \App\Support\ClinicalCompositeFields::audStatusOptions();
    $withStatus = (bool) ($fieldDefinition['with_status'] ?? true);
@endphp

<div class="clinical-composite-display">
    @if(collect($data['metrics'])->contains(fn ($row) => filled($row['value'] ?? null)))
        <div class="table-responsive mb-2">
            <table class="table table-sm table-bordered mb-0 clinical-kv-table">
                <tbody>
                    @foreach($data['metrics'] as $row)
                        @if(filled($row['value'] ?? null))
                            <tr>
                                <th class="text-muted small fw-semibold" style="width: 42%;">{{ $row['key'] }}</th>
                                <td>{{ $row['value'] }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($withStatus && filled($data['status']))
        <div class="small">
            <span class="text-muted fw-semibold">{{ __('workflow.fields.clinical_aud_status') }}:</span>
            <span class="badge bg-light text-dark border">{{ $statusOptions[$data['status']] ?? $data['status'] }}</span>
        </div>
    @endif
</div>
