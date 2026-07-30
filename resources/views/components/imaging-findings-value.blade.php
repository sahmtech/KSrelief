@props(['value' => null])

@php
    use App\Support\ClinicalValuePresenter;
    use App\Support\ScreeningFieldSupport;

    $sections = ScreeningFieldSupport::imagingFindingsDisplaySections($value);
@endphp

@if($sections === [])
    <span class="text-muted">—</span>
@else
    <div class="clinical-imaging-findings-value">
        @foreach($sections as $index => $section)
            @php
                $driveLabel = $section['modality'] === 'ct'
                    ? __('workflow.fields.imaging_ct_drive_link')
                    : __('workflow.fields.imaging_mri_drive_link');
                $drive = ClinicalValuePresenter::present($section['drive_link'], 'url', $driveLabel);
            @endphp
            <div class="imaging-findings-summary-card{{ $index > 0 ? ' mt-2' : '' }}">
                <div class="imaging-findings-summary-card__title">
                    <i class="ti ti-{{ $section['modality'] === 'ct' ? 'scan' : 'brain' }} me-1"></i>
                    {{ $section['title'] }}
                </div>

                @if($section['labels'] !== [])
                    <ul class="imaging-findings-summary-card__list">
                        @foreach($section['labels'] as $label)
                            <li>{{ $label }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(filled($section['notes']))
                    <div class="imaging-findings-summary-card__notes">
                        <span class="text-muted fw-semibold">{{ __('workflow.fields.imaging_notes') }}:</span>
                        <span class="text-break">{{ $section['notes'] }}</span>
                    </div>
                @endif

                @if(filled($drive['url'] ?? null))
                    <div class="imaging-findings-summary-card__drive small">
                        <span class="text-muted">{{ $driveLabel }}:</span>
                        <a href="{{ $drive['url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="clinical-link clinical-link--{{ $drive['variant'] }}">
                            <i class="ti ti-{{ $drive['icon'] }} clinical-link__icon"></i>
                            <span class="clinical-link__label">{{ $drive['label'] }}</span>
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
