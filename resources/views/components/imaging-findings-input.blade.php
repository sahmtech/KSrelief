@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    use App\Support\ImagingFindingsTreeSupport;
    use App\Support\ScreeningFieldSupport;

    $data = ScreeningFieldSupport::resolveImagingFindingsForForm(
        is_array(old($namePrefix)) ? old($namePrefix) : $savedValue
    );
    $modalities = ImagingFindingsTreeSupport::modalities();
    $ears = ImagingFindingsTreeSupport::ears();
@endphp

<div class="clinical-imaging-findings-tree"
     data-imaging-findings-tree
     data-name-prefix="{{ $namePrefix }}">
    <ul class="nav nav-pills imaging-tree-modality-tabs mb-3" role="tablist">
        @foreach($modalities as $modalityIndex => $modality)
            <li class="nav-item" role="presentation">
                <button type="button"
                        class="nav-link{{ $modalityIndex === 0 ? ' active' : '' }}"
                        data-imaging-modality-tab="{{ $modality }}"
                        role="tab"
                        aria-selected="{{ $modalityIndex === 0 ? 'true' : 'false' }}">
                    <i class="ti ti-{{ $modality === 'ct' ? 'scan' : 'brain' }} me-1"></i>
                    {{ strtoupper($modality) }}
                </button>
            </li>
        @endforeach
    </ul>

    @foreach($modalities as $modalityIndex => $modality)
        @php
            $tree = ImagingFindingsTreeSupport::tree($modality);
            $driveField = $modality === 'ct' ? 'ct_drive_link' : 'mri_drive_link';
            $notesField = $modality === 'ct' ? 'ct_notes' : 'mri_notes';
        @endphp
        <div class="imaging-tree-modality-panel{{ $modalityIndex === 0 ? '' : ' d-none' }}"
             data-imaging-modality-panel="{{ $modality }}">
            <ul class="nav nav-tabs imaging-tree-ear-tabs mb-3" role="tablist">
                @foreach($ears as $earIndex => $ear)
                    <li class="nav-item" role="presentation">
                        <button type="button"
                                class="nav-link{{ $earIndex === 0 ? ' active' : '' }}"
                                data-imaging-ear-tab="{{ $ear }}"
                                data-imaging-modality="{{ $modality }}"
                                role="tab"
                                aria-selected="{{ $earIndex === 0 ? 'true' : 'false' }}">
                            <i class="ti ti-ear me-1"></i>
                            {{ __('workflow.fields.imaging_ear_'.$ear) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            @foreach($ears as $earIndex => $ear)
                @php
                    $earData = $data[$ear] ?? [
                        'ct' => [],
                        'mri' => [],
                        'ct_notes' => '',
                        'mri_notes' => '',
                        'ct_drive_link' => '',
                        'mri_drive_link' => '',
                    ];
                    $selectedKeys = $earData[$modality] ?? [];
                    $driveLink = $earData[$driveField] ?? '';
                    $notes = $earData[$notesField] ?? '';
                    $summaryTitle = ScreeningFieldSupport::imagingModalityEarTitle($modality, $ear);
                @endphp
                <div class="imaging-tree-ear-panel{{ $earIndex === 0 ? '' : ' d-none' }}"
                     data-imaging-ear-panel="{{ $ear }}"
                     data-imaging-modality="{{ $modality }}"
                     data-summary-title="{{ $summaryTitle }}">
                    <div class="imaging-tree-panel card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <div class="imaging-tree-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <span class="small text-muted">{{ __('workflow.imaging_tree.select_hint') }}</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-imaging-tree-expand-all>
                                        {{ __('workflow.imaging_tree.expand_all') }}
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-imaging-tree-collapse-all>
                                        {{ __('workflow.imaging_tree.collapse_all') }}
                                    </button>
                                </div>
                            </div>

                            <div class="imaging-tree-root" data-imaging-tree-root>
                                @foreach($tree as $node)
                                    @include('components.partials.imaging-tree-node', [
                                        'node' => $node,
                                        'modality' => $modality,
                                        'ear' => $ear,
                                        'namePrefix' => $namePrefix,
                                        'selectedKeys' => $selectedKeys,
                                        'parentPath' => null,
                                        'depth' => 0,
                                    ])
                                @endforeach
                            </div>

                            <div class="mt-3">
                                <label class="form-label small fw-semibold mb-1">
                                    {{ __('workflow.fields.imaging_notes') }}
                                </label>
                                <textarea name="{{ $namePrefix }}[{{ $ear }}][{{ $notesField }}]"
                                          class="form-control form-control-sm"
                                          rows="2"
                                          placeholder="{{ __('workflow.fields.imaging_notes_placeholder') }}">{{ $notes }}</textarea>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <label class="form-label small text-muted mb-1">
                                    {{ $modality === 'ct' ? __('workflow.fields.imaging_ct_drive_link') : __('workflow.fields.imaging_mri_drive_link') }}
                                </label>
                                <input type="url"
                                       name="{{ $namePrefix }}[{{ $ear }}][{{ $driveField }}]"
                                       class="form-control form-control-sm"
                                       value="{{ $driveLink }}"
                                       placeholder="{{ __('workflow.links.drive_placeholder') }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="imaging-tree-overview mt-3"
         data-imaging-tree-overview>
        <div class="imaging-tree-overview__header">
            <i class="ti ti-list-check me-1"></i>
            {{ __('workflow.imaging_tree.overview_title') }}
        </div>
        <div class="imaging-tree-overview__body row g-2"
             data-imaging-tree-overview-body></div>
        <div class="text-muted small imaging-tree-overview__empty mt-2"
             data-imaging-tree-overview-empty>
            {{ __('workflow.imaging_tree.none_selected') }}
        </div>
    </div>
</div>
