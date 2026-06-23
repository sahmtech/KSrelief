@props(['value' => null])

@php
    $keys = config('patient_clinical.clinical_speech_follow_up_keys', ['Cap', 'SIR']);
    $data = \App\Support\ClinicalCompositeFields::normalizeSpeechFollowup($value, $keys);
    $assessmentOptions = \App\Support\ClinicalCompositeFields::speechAssessmentOptions();
@endphp

<div class="clinical-composite-display">
    @if(collect($data['metrics'])->contains(fn ($row) => filled($row['value'] ?? null)))
        <div class="table-responsive @if(filled($data['assessment'])) mb-2 @endif">
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

    @if(filled($data['assessment']))
        <div class="small">
            <span class="text-muted fw-semibold">{{ __('workflow.fields.clinical_speech_assessment') }}:</span>
            <span class="badge bg-light text-dark border">{{ $assessmentOptions[$data['assessment']] ?? \App\Support\ClinicalCompositeFields::speechAssessmentLabel((string) $data['assessment']) }}</span>
        </div>
    @endif
</div>
