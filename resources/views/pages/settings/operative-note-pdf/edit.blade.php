@extends('layouts.admin')

@section('title', __('settings.entities.operative_note_pdf.title'))

@section('content')
@php
    $defaults = old('defaults', $template['defaults'] ?? []);
    $paragraphs = old('narrative_paragraphs', $template['narrative_paragraphs'] ?? []);
    $orders = old('post_op_orders', $template['post_op_orders'] ?? []);
    $formatting = old('formatting', $template['formatting'] ?? []);
@endphp

<x-page-header
    :title="__('settings.entities.operative_note_pdf.title')"
    :subtitle="__('settings.entities.operative_note_pdf.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.settings'), 'url' => route('settings.dashboard')],
        ['label' => __('settings.entities.operative_note_pdf.title')],
    ]"
>
    <div class="d-flex flex-wrap gap-2">
        @if($template['is_custom'] ?? false)
            <span class="badge bg-success-subtle text-success border border-success-subtle align-self-center px-3 py-2">
                <i class="ti ti-check me-1"></i>{{ __('settings.entities.operative_note_pdf.status_custom') }}
            </span>
        @else
            <span class="badge bg-secondary-subtle text-secondary border align-self-center px-3 py-2">
                <i class="ti ti-template me-1"></i>{{ __('settings.entities.operative_note_pdf.status_default') }}
            </span>
        @endif
        <a href="{{ route('settings.dashboard') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('common.cancel') }}
        </a>
    </div>
</x-page-header>

@if($template['updated_at'] ?? null)
    <div class="alert alert-light border mb-4 py-2 small text-muted">
        <i class="ti ti-clock me-1"></i>
        {{ __('settings.entities.operative_note_pdf.last_updated', [
            'date' => $template['updated_at'],
            'by' => $template['updated_by_name'] ?? '—',
        ]) }}
    </div>
@endif

<form method="POST" action="{{ route('settings.operative-note-pdf.update') }}" id="operativeNotePdfForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-xl-8">
            {{-- Defaults --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="ti ti-forms"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ __('settings.entities.operative_note_pdf.sections.defaults') }}</h6>
                            <div class="small text-muted">{{ __('settings.entities.operative_note_pdf.sections.defaults_hint') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.preop_diagnosis') }}</label>
                            <input type="text" name="defaults[preop_diagnosis]" class="form-control @error('defaults.preop_diagnosis') is-invalid @enderror"
                                   value="{{ $defaults['preop_diagnosis'] ?? '' }}" @disabled(! $canUpdate)>
                            @error('defaults.preop_diagnosis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.postop_diagnosis') }}</label>
                            <input type="text" name="defaults[postop_diagnosis]" class="form-control @error('defaults.postop_diagnosis') is-invalid @enderror"
                                   value="{{ $defaults['postop_diagnosis'] ?? '' }}" @disabled(! $canUpdate)>
                            @error('defaults.postop_diagnosis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.facial_nerve_monitor') }}</label>
                            <input type="text" name="defaults[facial_nerve_monitor]" class="form-control @error('defaults.facial_nerve_monitor') is-invalid @enderror"
                                   value="{{ $defaults['facial_nerve_monitor'] ?? '' }}" @disabled(! $canUpdate)>
                            @error('defaults.facial_nerve_monitor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.local_anesthesia') }}</label>
                            <input type="text" name="defaults[local_anesthesia]" class="form-control @error('defaults.local_anesthesia') is-invalid @enderror"
                                   value="{{ $defaults['local_anesthesia'] ?? '' }}" @disabled(! $canUpdate)>
                            @error('defaults.local_anesthesia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.bed_status') }}</label>
                            <input type="text" name="defaults[bed_status]" class="form-control @error('defaults.bed_status') is-invalid @enderror"
                                   value="{{ $defaults['bed_status'] ?? '' }}" @disabled(! $canUpdate)>
                            @error('defaults.bed_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Narrative --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-align-left"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ __('settings.entities.operative_note_pdf.sections.narrative') }}</h6>
                                <div class="small text-muted">{{ __('settings.entities.operative_note_pdf.sections.narrative_hint') }}</div>
                            </div>
                        </div>
                        @if($canUpdate)
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addNarrativeParagraph">
                                <i class="ti ti-plus me-1"></i>{{ __('settings.entities.operative_note_pdf.actions.add_paragraph') }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body" id="narrativeParagraphs">
                    @foreach($paragraphs as $index => $paragraph)
                        <div class="narrative-item mb-3" data-narrative-item>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold mb-0 small text-muted">
                                    {{ __('settings.entities.operative_note_pdf.fields.paragraph') }} #{{ $index + 1 }}
                                </label>
                                @if($canUpdate)
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-narrative>
                                        <i class="ti ti-trash"></i>
                                    </button>
                                @endif
                            </div>
                            <textarea name="narrative_paragraphs[]" class="form-control" rows="4" @disabled(! $canUpdate)>{{ $paragraph }}</textarea>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Post-op orders --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="ti ti-list-check"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ __('settings.entities.operative_note_pdf.sections.orders') }}</h6>
                                <div class="small text-muted">{{ __('settings.entities.operative_note_pdf.sections.orders_hint') }}</div>
                            </div>
                        </div>
                        @if($canUpdate)
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPostOpOrder">
                                <i class="ti ti-plus me-1"></i>{{ __('settings.entities.operative_note_pdf.actions.add_order') }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body" id="postOpOrders">
                    @foreach($orders as $index => $order)
                        <div class="input-group mb-2" data-order-item>
                            <span class="input-group-text">{{ $index + 1 }}</span>
                            <input type="text" name="post_op_orders[]" class="form-control" value="{{ $order }}" @disabled(! $canUpdate)>
                            @if($canUpdate)
                                <button type="button" class="btn btn-outline-danger" data-remove-order>
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Formatting --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="ti ti-adjustments-horizontal"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-semibold">{{ __('settings.entities.operative_note_pdf.sections.formatting') }}</h6>
                            <div class="small text-muted">{{ __('settings.entities.operative_note_pdf.sections.formatting_hint') }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.page_margin_mm') }}</label>
                            <input type="number" step="0.5" min="5" max="20" name="formatting[page_margin_mm]" class="form-control"
                                   value="{{ $formatting['page_margin_mm'] ?? 10 }}" @disabled(! $canUpdate)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.border_width_pt') }}</label>
                            <input type="number" step="0.1" min="0.5" max="5" name="formatting[border_width_pt]" class="form-control"
                                   value="{{ $formatting['border_width_pt'] ?? 2.5 }}" @disabled(! $canUpdate)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.page_padding_bottom_mm') }}</label>
                            <input type="number" step="0.5" min="8" max="40" name="formatting[page_padding_bottom_mm]" class="form-control"
                                   value="{{ $formatting['page_padding_bottom_mm'] ?? 22 }}" @disabled(! $canUpdate)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.body_font_size_pt') }}</label>
                            <input type="number" step="0.5" min="9" max="14" name="formatting[body_font_size_pt]" class="form-control"
                                   value="{{ $formatting['body_font_size_pt'] ?? 11 }}" @disabled(! $canUpdate)>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('settings.entities.operative_note_pdf.fields.title_font_size_pt') }}</label>
                            <input type="number" step="0.5" min="12" max="22" name="formatting[title_font_size_pt]" class="form-control"
                                   value="{{ $formatting['title_font_size_pt'] ?? 16 }}" @disabled(! $canUpdate)>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="showLogo"
                                       name="formatting[show_logo]" value="1"
                                       @checked(! empty($formatting['show_logo']))
                                       @disabled(! $canUpdate)>
                                <label class="form-check-label fw-semibold" for="showLogo">
                                    {{ __('settings.entities.operative_note_pdf.fields.show_logo') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($canUpdate)
                <div class="d-flex flex-wrap gap-2 sticky-bottom bg-body py-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}
                    </button>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 1rem;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="ti ti-code me-1 text-primary"></i>
                        {{ __('settings.entities.operative_note_pdf.sections.tokens') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">{{ __('settings.entities.operative_note_pdf.sections.tokens_hint') }}</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach($tokens as $token)
                            <div class="border rounded-3 p-2">
                                <code class="small user-select-all">{{ $token['token'] }}</code>
                                <div class="fw-semibold small mt-1">{{ $token['label'] }}</div>
                                <div class="text-muted" style="font-size: .75rem;">{{ $token['description'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if($canUpdate)
                        <hr>
                        <p class="small text-muted mb-2">{{ __('settings.entities.operative_note_pdf.reset_hint') }}</p>
                        <button type="submit"
                                form="operativeNotePdfResetForm"
                                class="btn btn-outline-secondary w-100"
                                onclick="return confirm(@json(__('settings.entities.operative_note_pdf.confirm_reset')))">
                            <i class="ti ti-restore me-1"></i>{{ __('settings.entities.operative_note_pdf.actions.reset') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

@if($canUpdate)
<form method="POST" action="{{ route('settings.operative-note-pdf.reset') }}" id="operativeNotePdfResetForm" class="d-none">
    @csrf
</form>
@endif

<template id="narrativeParagraphTemplate">
    <div class="narrative-item mb-3" data-narrative-item>
        <div class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label fw-semibold mb-0 small text-muted">
                {{ __('settings.entities.operative_note_pdf.fields.paragraph') }}
            </label>
            <button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-narrative>
                <i class="ti ti-trash"></i>
            </button>
        </div>
        <textarea name="narrative_paragraphs[]" class="form-control" rows="4"></textarea>
    </div>
</template>

<template id="postOpOrderTemplate">
    <div class="input-group mb-2" data-order-item>
        <span class="input-group-text">#</span>
        <input type="text" name="post_op_orders[]" class="form-control" value="">
        <button type="button" class="btn btn-outline-danger" data-remove-order>
            <i class="ti ti-trash"></i>
        </button>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const narrativeRoot = document.getElementById('narrativeParagraphs');
    const ordersRoot = document.getElementById('postOpOrders');
    const narrativeTpl = document.getElementById('narrativeParagraphTemplate');
    const orderTpl = document.getElementById('postOpOrderTemplate');

    document.getElementById('addNarrativeParagraph')?.addEventListener('click', function () {
        narrativeRoot.appendChild(narrativeTpl.content.cloneNode(true));
    });

    document.getElementById('addPostOpOrder')?.addEventListener('click', function () {
        const node = orderTpl.content.cloneNode(true);
        ordersRoot.appendChild(node);
        renumberOrders();
    });

    narrativeRoot?.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-narrative]');
        if (!btn) return;
        const items = narrativeRoot.querySelectorAll('[data-narrative-item]');
        if (items.length <= 1) return;
        btn.closest('[data-narrative-item]')?.remove();
    });

    ordersRoot?.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-order]');
        if (!btn) return;
        const items = ordersRoot.querySelectorAll('[data-order-item]');
        if (items.length <= 1) return;
        btn.closest('[data-order-item]')?.remove();
        renumberOrders();
    });

    function renumberOrders() {
        ordersRoot.querySelectorAll('[data-order-item]').forEach(function (item, i) {
            const badge = item.querySelector('.input-group-text');
            if (badge) badge.textContent = String(i + 1);
        });
    }
});
</script>
@endpush
