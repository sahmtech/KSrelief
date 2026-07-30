@props([
    'namePrefix',
    'type',
    'assessment' => [],
])

@php
    $metrics = [
        'srt' => 'SRT',
        'wrs' => 'WRS',
    ];
@endphp

<div class="hearing-speech-section">
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 hearing-speech-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 11rem;">{{ __('workflow.hearing_assessment.table.ear') }}</th>
                    @foreach($metrics as $metricLabel)
                        <th class="text-center small">{{ $metricLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(['right', 'left'] as $ear)
                    <tr>
                        <td class="fw-semibold small text-nowrap">
                            {{ __('workflow.fields.imaging_ear_'.$ear) }}
                        </td>
                        @foreach(array_keys($metrics) as $metricKey)
                            @php
                                $row = $assessment[$metricKey] ?? ['right' => '', 'left' => ''];
                            @endphp
                            <td class="p-1">
                                <input type="text"
                                       class="form-control form-control-sm text-center"
                                       name="{{ $namePrefix }}[assessments][{{ $type }}][{{ $metricKey }}][{{ $ear }}]"
                                       value="{{ $row[$ear] ?? '' }}"
                                       placeholder="—">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
