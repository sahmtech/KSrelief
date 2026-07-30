@props([
    'namePrefix',
    'type',
    'sectionKey',
    'sectionLabel',
    'grid' => [],
])

@php
    use App\Support\HearingAssessmentSupport;

    $frequencies = HearingAssessmentSupport::frequencies();
@endphp

<div class="hearing-frequency-section mb-3">
    <div class="small fw-semibold text-muted mb-2">{{ $sectionLabel }}</div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 hearing-frequency-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 11rem;">{{ __('workflow.hearing_assessment.table.ear') }}</th>
                    @foreach($frequencies as $frequency)
                        <th class="text-center small text-nowrap">{{ $frequency }} Hz</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach(['right', 'left'] as $ear)
                    <tr>
                        <td class="fw-semibold small text-nowrap">
                            {{ __('workflow.fields.imaging_ear_'.$ear) }}
                        </td>
                        @foreach($frequencies as $frequency)
                            @php
                                $row = $grid[$frequency] ?? ['right' => '', 'left' => ''];
                            @endphp
                            <td class="p-1">
                                <input type="text"
                                       class="form-control form-control-sm text-center"
                                       name="{{ $namePrefix }}[assessments][{{ $type }}][{{ $sectionKey }}][{{ $frequency }}][{{ $ear }}]"
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
