@props([
    'campaign',
    'summaries' => [],
])

@if(! empty($summaries))
@include('pages.patients.partials.clinical-fallback-styles')
<div class="campaign-operation-defaults" id="operation-record-defaults">
    <x-card :title="__('campaigns.sections.operation_defaults')">
        <x-slot:actions>
            @can('update', $campaign)
                <a href="{{ route('campaigns.edit', $campaign) }}#operation-record-defaults" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-pencil me-1"></i>{{ __('common.edit') }}
                </a>
            @endcan
        </x-slot:actions>

        <p class="text-muted small mb-3">{{ __('campaigns.hints.operation_defaults_show') }}</p>

        <ul class="nav nav-tabs operation-form-tabs mb-3" role="tablist">
            @foreach($summaries as $summary)
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link @if($loop->first) active @endif"
                            role="tab"
                            data-bs-toggle="tab"
                            data-bs-target="#campaignOpShow{{ $summary['company']->id }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        <span style="color: {{ $summary['company']->color }}; font-weight: 600;">{{ $summary['company']->name }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($summaries as $summary)
                <div class="tab-pane fade @if($loop->first) show active @endif"
                     id="campaignOpShow{{ $summary['company']->id }}"
                     role="tabpanel">
                    <dl class="row small mb-0">
                        <dt class="col-sm-4 fw-semibold">{{ __('workflow.fields.electrode_type') }}</dt>
                        <dd class="col-sm-8">{{ $summary['electrode_name'] }}</dd>

                        <dt class="col-sm-4 fw-semibold">{{ __('workflow.fields.insertion_approach') }}</dt>
                        <dd class="col-sm-8">{{ $summary['approach_name'] }}</dd>

                        <dt class="col-sm-4 fw-semibold">{{ __('workflow.operation.fields.insertion_depth') }}</dt>
                        <dd class="col-sm-8">
                            <x-operation-insertion-depth-value :value="$summary['insertion_depth']" />
                        </dd>

                        <dt class="col-sm-4 fw-semibold">{{ __('workflow.operation.fields.audio_test') }}</dt>
                        <dd class="col-sm-8">
                            <x-operation-audio-test-value :value="$summary['audio_test']" />
                        </dd>

                        <dt class="col-sm-4 fw-semibold">{{ __('workflow.fields.intra_op_findings') }}</dt>
                        <dd class="col-sm-8">
                            <x-operation-intra-op-findings-value :value="$summary['intra_op_findings']" />
                        </dd>
                    </dl>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
@endif
