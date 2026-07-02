@props(['value' => null])

@php
    use App\Support\ClinicalValuePresenter;
    use App\Support\ScreeningFieldSupport;

    $sections = ScreeningFieldSupport::imagingFindingsDisplaySections($value);
@endphp

@if($sections === [])
    <span class="text-muted">—</span>
@else
    <div class="clinical-imaging-findings-value text-break" style="white-space: pre-line;">
        @foreach($sections as $index => $section)
            @php
                $ctDrive = ClinicalValuePresenter::present($section['ct_drive_link'], 'url', __('workflow.fields.imaging_ct_drive_link'));
                $mriDrive = ClinicalValuePresenter::present($section['mri_drive_link'], 'url', __('workflow.fields.imaging_mri_drive_link'));
            @endphp
            @if($index > 0)
                <div class="mt-2"></div>
            @endif
            <div class="fw-semibold small">{{ __('workflow.fields.imaging_ear_'.$section['ear']) }}</div>
            @if($section['ct_labels'] !== [])
                <div class="small">{{ __('workflow.fields.ct_findings') }}: {{ implode(', ', $section['ct_labels']) }}</div>
            @endif
            @if(filled($ctDrive['url'] ?? null))
                <div class="small">
                    {{ __('workflow.fields.imaging_ct_drive_link') }}:
                    <a href="{{ $ctDrive['url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="clinical-link clinical-link--{{ $ctDrive['variant'] }}">
                        <i class="ti ti-{{ $ctDrive['icon'] }} clinical-link__icon"></i>
                        <span class="clinical-link__label">{{ $ctDrive['label'] }}</span>
                    </a>
                </div>
            @endif
            @if($section['mri_labels'] !== [])
                <div class="small">{{ __('workflow.fields.mri_findings') }}: {{ implode(', ', $section['mri_labels']) }}</div>
            @endif
            @if(filled($mriDrive['url'] ?? null))
                <div class="small">
                    {{ __('workflow.fields.imaging_mri_drive_link') }}:
                    <a href="{{ $mriDrive['url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="clinical-link clinical-link--{{ $mriDrive['variant'] }}">
                        <i class="ti ti-{{ $mriDrive['icon'] }} clinical-link__icon"></i>
                        <span class="clinical-link__label">{{ $mriDrive['label'] }}</span>
                    </a>
                </div>
            @endif
        @endforeach
    </div>
@endif
