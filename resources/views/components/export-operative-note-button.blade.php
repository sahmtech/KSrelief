@once
@push('styles')
<style>
    .btn-operative-note-export {
        --on-green: #0d5c3d;
        --on-green-deep: #09452e;
        --on-gold: #b8965a;
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border: 0;
        color: #fff !important;
        background: linear-gradient(135deg, var(--on-green) 0%, var(--on-green-deep) 58%, #0a3d2a 100%);
        box-shadow: 0 8px 18px rgba(13, 92, 61, .22);
        font-weight: 600;
        letter-spacing: .01em;
        overflow: hidden;
    }
    .btn-operative-note-export::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--on-gold);
    }
    .btn-operative-note-export:hover,
    .btn-operative-note-export:focus {
        color: #fff !important;
        background: linear-gradient(135deg, #12724c 0%, var(--on-green) 55%, var(--on-green-deep) 100%);
        box-shadow: 0 10px 22px rgba(13, 92, 61, .28);
    }
    .btn-operative-note-export .on-icon {
        width: 1.55rem;
        height: 1.55rem;
        border-radius: .45rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(184, 150, 90, .22);
        color: #f3e2ba;
        flex-shrink: 0;
    }
    .operative-note-modal .modal-content {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, .18);
    }
    .operative-note-modal__hero {
        background: linear-gradient(135deg, #0d5c3d 0%, #0a4630 70%, #b8965a 160%);
        color: #fff;
        padding: 1.15rem 1.25rem;
    }
    .operative-note-modal__hero-title {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }
    .operative-note-modal__hero-sub {
        margin: .25rem 0 0;
        opacity: .9;
        font-size: .875rem;
    }
    .operative-note-modal__latest {
        border: 1px solid rgba(13, 92, 61, .14);
        background: rgba(13, 92, 61, .04);
        border-radius: .85rem;
        padding: 1rem;
    }
    .operative-note-modal__latest-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #0d5c3d;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .operative-note-modal__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem .75rem;
        color: #475569;
        font-size: .875rem;
        margin-bottom: .9rem;
    }
    .operative-note-modal__meta span {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }
    .operative-note-modal__divider {
        display: flex;
        align-items: center;
        gap: .75rem;
        color: #64748b;
        font-size: .8125rem;
        font-weight: 600;
        margin: 1.15rem 0 .85rem;
    }
    .operative-note-modal__divider::before,
    .operative-note-modal__divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    .operative-note-modal table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #64748b;
        font-weight: 700;
        white-space: nowrap;
    }
    .operative-note-modal .btn-export-row {
        --bs-btn-color: #0d5c3d;
        --bs-btn-border-color: rgba(13, 92, 61, .35);
        --bs-btn-hover-bg: #0d5c3d;
        --bs-btn-hover-border-color: #0d5c3d;
        --bs-btn-hover-color: #fff;
        font-weight: 600;
    }
</style>
@endpush
@endonce

<button type="button"
        class="btn btn-operative-note-export btn-{{ $size }}"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}">
    <span class="on-icon"><i class="ti ti-file-type-pdf"></i></span>
    <span>{{ __('patients.operative_note_export.button') }}</span>
</button>

<div class="modal fade modal-admin operative-note-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="operative-note-modal__hero d-flex align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="on-icon" style="width:2.5rem;height:2.5rem;border-radius:.7rem;background:rgba(255,255,255,.12);display:inline-flex;align-items:center;justify-content:center;">
                        <i class="ti ti-file-certificate fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="operative-note-modal__hero-title" id="{{ $modalId }}Label">
                            {{ __('patients.operative_note_export.modal_title') }}
                        </h5>
                        <p class="operative-note-modal__hero-sub">
                            {{ __('patients.operative_note_export.modal_subtitle') }}
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>

            <div class="modal-body p-3 p-md-4">
                @if($latest)
                    <div class="operative-note-modal__latest">
                        <div class="operative-note-modal__latest-label">
                            <i class="ti ti-sparkles me-1"></i>{{ __('patients.operative_note_export.latest_label') }}
                        </div>
                        <div class="fw-semibold mb-1">{{ $latest['date'] }}</div>
                        <div class="operative-note-modal__meta">
                            <span><i class="ti ti-user-heart"></i>{{ $latest['surgeon'] }}</span>
                            <span><i class="ti ti-ear"></i>{{ $latest['side'] }}</span>
                            <span><i class="ti ti-building-hospital"></i>{{ $latest['company'] }}</span>
                            <span><i class="ti ti-cpu"></i>{{ $latest['implant_type'] }}</span>
                        </div>
                        <a href="{{ $latest['export_url'] }}" class="btn btn-operative-note-export">
                            <span class="on-icon"><i class="ti ti-download"></i></span>
                            <span>{{ __('patients.operative_note_export.export_latest') }}</span>
                        </a>
                    </div>
                @endif

                @if($operations->count() > 1)
                    <div class="operative-note-modal__divider">
                        {{ __('patients.operative_note_export.or_choose') }}
                    </div>

                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('patients.operative_note_export.columns.date') }}</th>
                                    <th>{{ __('patients.operative_note_export.columns.surgeon') }}</th>
                                    <th>{{ __('patients.operative_note_export.columns.side') }}</th>
                                    <th>{{ __('patients.operative_note_export.columns.company') }}</th>
                                    <th>{{ __('patients.operative_note_export.columns.implant') }}</th>
                                    <th class="text-end">{{ __('patients.operative_note_export.columns.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($operations as $index => $op)
                                    <tr @class(['table-success' => $index === 0])>
                                        <td class="text-nowrap fw-semibold">
                                            {{ $op['date'] }}
                                            @if($index === 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">
                                                    {{ __('patients.operative_note_export.latest_badge') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $op['surgeon'] }}</td>
                                        <td>{{ $op['side'] }}</td>
                                        <td>{{ $op['company'] }}</td>
                                        <td>{{ $op['implant_type'] }}</td>
                                        <td class="text-end">
                                            <a href="{{ $op['export_url'] }}" class="btn btn-sm btn-outline-success btn-export-row">
                                                <i class="ti ti-file-type-pdf me-1"></i>{{ __('patients.operative_note_export.export_row') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
