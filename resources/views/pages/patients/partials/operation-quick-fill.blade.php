@props([
    'companies',
    'quickFillUrl' => '',
])

<div class="operation-quick-fill"
     data-operation-quick-fill
     data-url="{{ $quickFillUrl }}"
     data-msg-applied="{{ __('workflow.operation.quick_fill.applied') }}"
     data-msg-load-failed="{{ __('workflow.operation.quick_fill.load_failed') }}"
     data-msg-success="{{ __('messages.success') }}"
     data-msg-error="{{ __('messages.error') }}"
     data-msg-apply="{{ __('workflow.operation.quick_fill.apply') }}"
     data-msg-my-template="{{ __('workflow.operation.quick_fill.my_template') }}"
     data-msg-campaign-default="{{ __('workflow.operation.quick_fill.campaign_default') }}"
     data-label-electrode="{{ __('workflow.fields.electrode_type') }}"
     data-label-insertion-approach="{{ __('workflow.fields.insertion_approach') }}"
     data-label-insertion-depth="{{ __('workflow.operation.fields.insertion_depth') }}"
     data-label-intra-op="{{ __('workflow.fields.intra_op_findings') }}"
     data-summary-empty="{{ __('workflow.operation.quick_fill.not_set') }}">
    <p class="text-muted small mb-3">{{ __('workflow.operation.quick_fill.intro') }}</p>

    <div data-quick-fill-companies-step>
        <h6 class="fw-semibold small text-uppercase text-muted mb-2">
            {{ __('workflow.operation.quick_fill.choose_company') }}
        </h6>
        <div class="operation-quick-fill__companies">
            @forelse($companies as $company)
                <button type="button"
                        class="operation-quick-fill__company-card"
                        data-company-id="{{ $company->id }}"
                        style="--company-color: {{ $company->color }};">
                    <span class="operation-quick-fill__company-dot"></span>
                    <span class="operation-quick-fill__company-name">{{ $company->name }}</span>
                </button>
            @empty
                <p class="text-muted mb-0">{{ __('workflow.operation.quick_fill.no_companies') }}</p>
            @endforelse
        </div>
    </div>

    <div data-quick-fill-presets-step hidden>
        <div class="operation-quick-fill__presets-header">
            <div>
                <button type="button" class="btn btn-link btn-sm px-0 text-decoration-none" data-quick-fill-back>
                    <i class="ti ti-arrow-left me-1"></i>{{ __('workflow.operation.quick_fill.back_to_companies') }}
                </button>
                <h6 class="fw-semibold mb-0 mt-1" data-quick-fill-company-title></h6>
                <p class="text-muted small mb-0">{{ __('workflow.operation.quick_fill.choose_preset') }}</p>
            </div>
        </div>

        <div class="operation-quick-fill__loading text-center py-4" data-quick-fill-loading hidden>
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2 text-muted small">{{ __('workflow.operation.quick_fill.loading') }}</span>
        </div>

        <div class="operation-quick-fill__grid" data-quick-fill-grid></div>

        <div class="alert alert-light border mb-0" data-quick-fill-empty hidden>
            {{ __('workflow.operation.quick_fill.no_presets') }}
        </div>
    </div>
</div>
