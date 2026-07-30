@extends('layouts.admin')

@section('title', __('workflow.records.add') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="__('workflow.records.add')"
    :subtitle="$patient->patient_name"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.medical_records'), 'url' => route('patients.records.index', $patient)],
        ['label' => __('workflow.records.add')],
    ]"
/>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-file-plus me-2 text-primary"></i>
            {{ __('workflow.records.add') }}
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('patients.records.store', $patient) }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('pages.patients.records._form', [
                'patient'                         => $patient,
                'stages'                          => $stages,
                'stageFields'                     => $stageFields,
                'stageCode'                       => $stageCode,
                'teamMembers'                     => $teamMembers,
                'selectedStageId'                 => $selectedStageId ?? null,
                'enableFollowUpTemplateActions'   => true,
                'hasFollowUpDefaults'             => $hasFollowUpDefaults ?? false,
                'enableOperationTemplateActions'  => true,
                'hasOperationDefaults'            => $hasOperationDefaults ?? false,
                'enablePreOperationTemplateActions' => true,
                'hasPreOperationDefaults'         => $hasPreOperationDefaults ?? false,
                'enablePostOperationTemplateActions' => true,
                'hasPostOperationDefaults'        => $hasPostOperationDefaults ?? false,
                'hasCampaignOperationDefaults'    => $hasCampaignOperationDefaults ?? false,
            ])

            <div class="record-form-actions d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('common.save') }}
                </button>
                <button type="submit"
                        id="operationSaveExportPdfBtn"
                        name="export_pdf"
                        value="1"
                        class="btn btn-success"
                        @hidden(($stageCode ?? '') !== 'operation')>
                    <i class="ti ti-file-type-pdf me-1"></i>{{ __('workflow.operation.save_and_export_pdf') }}
                </button>
                <button type="submit"
                        id="operationSaveDefaultBtn"
                        name="save_operation_defaults"
                        value="1"
                        class="btn btn-outline-secondary"
                        @hidden(($stageCode ?? '') !== 'operation')>
                    <i class="ti ti-bookmark me-1"></i>{{ __('workflow.operation.save_as_default') }}
                </button>
                <button type="submit"
                        id="followUpSaveDefaultBtn"
                        name="save_follow_up_defaults"
                        value="1"
                        class="btn btn-outline-secondary"
                        @hidden(($stageCode ?? '') !== 'follow_up')>
                    <i class="ti ti-bookmark me-1"></i>{{ __('workflow.follow_up.save_as_default') }}
                </button>
                <button type="submit"
                        id="preOpSaveDefaultBtn"
                        name="save_pre_operation_defaults"
                        value="1"
                        class="btn btn-outline-secondary"
                        @hidden(($stageCode ?? '') !== 'pre_operation')>
                    <i class="ti ti-bookmark me-1"></i>{{ __('workflow.pre_op.save_as_default') }}
                </button>
                <button type="submit"
                        id="postOpSaveDefaultBtn"
                        name="save_post_operation_defaults"
                        value="1"
                        class="btn btn-outline-secondary"
                        @hidden(($stageCode ?? '') !== 'post_operation')>
                    <i class="ti ti-bookmark me-1"></i>{{ __('workflow.post_op.save_as_default') }}
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-light ms-md-auto">
                    {{ __('common.cancel') }}
                </a>
            </div>
            <p id="operationDefaultHint" class="form-text mt-2 mb-0" @hidden(($stageCode ?? '') !== 'operation')>
                {{ __('workflow.operation.save_as_default_hint') }}
            </p>
            <p id="operationExportPdfHint" class="form-text mt-1 mb-0" @hidden(($stageCode ?? '') !== 'operation')>
                {{ __('workflow.operation.export_pdf_hint') }}
            </p>
            <p id="followUpDefaultHint" class="form-text mt-2 mb-0" @hidden(($stageCode ?? '') !== 'follow_up')>
                {{ __('workflow.follow_up.save_as_default_hint') }}
            </p>
            <p id="preOpDefaultHint" class="form-text mt-2 mb-0" @hidden(($stageCode ?? '') !== 'pre_operation')>
                {{ __('workflow.pre_op.save_as_default_hint') }}
            </p>
            <p id="postOpDefaultHint" class="form-text mt-2 mb-0" @hidden(($stageCode ?? '') !== 'post_operation')>
                {{ __('workflow.post_op.save_as_default_hint') }}
            </p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.configureCampaignOperationDefaultsActions?.({
        url: @json($campaignOperationDefaultsUrl ?? ''),
        hasDefaults: @json($hasCampaignOperationDefaults ?? false),
        messages: {
            successTitle: @json(__('messages.success')),
            errorTitle: @json(__('messages.error')),
            selectCompany: @json(__('workflow.operation.select_company_for_campaign_defaults')),
            noCampaignDefaults: @json(__('workflow.operation.no_campaign_defaults')),
            campaignDefaultsLoaded: @json(__('workflow.operation.campaign_defaults_loaded')),
            loadFailed: @json(__('workflow.operation.campaign_defaults_load_failed')),
        },
    });

    window.configureOperationTemplateActions?.({
        saveBtn: document.getElementById('operationSaveDefaultBtn'),
        hint: document.getElementById('operationDefaultHint'),
        stageSelect: document.getElementById('stageSelect'),
        url: @json($operationDefaultsUrl ?? ''),
        hasDefaults: @json($hasOperationDefaults ?? false),
        messages: {
            successTitle: @json(__('messages.success')),
            errorTitle: @json(__('messages.error')),
            noTemplate: @json(__('workflow.operation.no_template')),
            templateLoaded: @json(__('workflow.operation.template_loaded')),
            loadFailed: @json(__('workflow.operation.template_load_failed')),
        },
    });

    window.configureFollowUpTemplateActions?.({
        saveBtn: document.getElementById('followUpSaveDefaultBtn'),
        hint: document.getElementById('followUpDefaultHint'),
        stageSelect: document.getElementById('stageSelect'),
        url: @json($followUpDefaultsUrl ?? ''),
        hasDefaults: @json($hasFollowUpDefaults ?? false),
        messages: {
            successTitle: @json(__('messages.success')),
            errorTitle: @json(__('messages.error')),
            noTemplate: @json(__('workflow.follow_up.no_template')),
            templateLoaded: @json(__('workflow.follow_up.template_loaded')),
            loadFailed: @json(__('workflow.follow_up.template_load_failed')),
        },
    });

    window.configurePreOperationTemplateActions?.({
        saveBtn: document.getElementById('preOpSaveDefaultBtn'),
        hint: document.getElementById('preOpDefaultHint'),
        stageSelect: document.getElementById('stageSelect'),
        url: @json($preOperationDefaultsUrl ?? ''),
        hasDefaults: @json($hasPreOperationDefaults ?? false),
        messages: {
            successTitle: @json(__('messages.success')),
            errorTitle: @json(__('messages.error')),
            noTemplate: @json(__('workflow.pre_op.no_template')),
            templateLoaded: @json(__('workflow.pre_op.template_loaded')),
            loadFailed: @json(__('workflow.pre_op.template_load_failed')),
        },
    });

    window.configurePostOperationTemplateActions?.({
        saveBtn: document.getElementById('postOpSaveDefaultBtn'),
        hint: document.getElementById('postOpDefaultHint'),
        stageSelect: document.getElementById('stageSelect'),
        url: @json($postOperationDefaultsUrl ?? ''),
        hasDefaults: @json($hasPostOperationDefaults ?? false),
        messages: {
            successTitle: @json(__('messages.success')),
            errorTitle: @json(__('messages.error')),
            noTemplate: @json(__('workflow.post_op.no_template')),
            templateLoaded: @json(__('workflow.post_op.template_loaded')),
            loadFailed: @json(__('workflow.post_op.template_load_failed')),
        },
    });

    const exportPdfBtn = document.getElementById('operationSaveExportPdfBtn');
    const exportPdfHint = document.getElementById('operationExportPdfHint');
    const stageSelect = document.getElementById('stageSelect');
    const syncOperationExportPdf = () => {
        const code = stageSelect?.selectedOptions?.[0]?.dataset?.code || '';
        const isOperation = code === 'operation';
        if (exportPdfBtn) exportPdfBtn.hidden = !isOperation;
        if (exportPdfHint) exportPdfHint.hidden = !isOperation;
    };
    stageSelect?.addEventListener('change', syncOperationExportPdf);
    syncOperationExportPdf();
});
</script>
@endpush
