@extends('layouts.admin')

@section('title', __('workflow.title') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="$patient->patient_name"
    :subtitle="__('workflow.title')"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.title')],
    ]"
/>

<div class="row g-4">
    {{-- Left: Current Stage Card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    @if($patient->currentStage)
                        <span class="badge rounded-pill fs-6 px-4 py-2"
                              style="background-color: {{ $patient->currentStage->color ?? '#3B82F6' }}; color: #fff;">
                            {{ $patient->currentStage->name }}
                        </span>
                    @else
                        <span class="badge bg-secondary rounded-pill fs-6 px-4 py-2">
                            {{ __('workflow.no_stage') }}
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-3">{{ __('workflow.current_stage') }}</p>
                <p class="text-muted small">
                    {{ __('workflow.last_updated') }}:
                    {{ $patient->updated_at?->format('d M Y, H:i') ?? '—' }}
                </p>

                @can('changeStage', $patient)
                <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#changeStageModal">
                    <i class="ti ti-transfer me-1"></i> {{ __('workflow.change_stage') }}
                </button>
                @endcan
            </div>
        </div>

        {{-- Back to Patient --}}
        <div class="mt-3">
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary w-100">
                <i class="ti ti-arrow-left me-1"></i> {{ __('patients.show.back') }}
            </a>
        </div>
    </div>

    {{-- Right: Workflow Timeline --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-timeline me-2 text-primary"></i>
                    {{ __('workflow.timeline.title') }}
                </h6>
            </div>
            <div class="card-body py-4">
                <x-workflow-timeline :timeline="$timeline" />
            </div>
        </div>
    </div>
</div>

{{-- Change Stage Modal --}}
@can('changeStage', $patient)
<div class="modal fade" id="changeStageModal" tabindex="-1" aria-labelledby="changeStageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('patients.workflow.change-stage', $patient) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="changeStageModalLabel">
                        <i class="ti ti-transfer me-2 text-primary"></i>
                        {{ __('workflow.change_stage_modal.title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('workflow.change_stage_modal.new_stage') }} <span class="text-danger">*</span></label>
                        <select name="to_stage_id" class="form-select @error('to_stage_id') is-invalid @enderror" required>
                            <option value="">— {{ __('common.select') }} —</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}"
                                    {{ $patient->current_stage_id == $stage->id ? 'disabled' : '' }}>
                                    {{ $stage->name }}
                                    {{ $patient->current_stage_id == $stage->id ? '(' . __('workflow.timeline.current') . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('to_stage_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('workflow.change_stage_modal.notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="{{ __('workflow.change_stage_modal.notes_hint') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ __('workflow.change_stage_modal.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i>
                        {{ __('workflow.change_stage_modal.confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
