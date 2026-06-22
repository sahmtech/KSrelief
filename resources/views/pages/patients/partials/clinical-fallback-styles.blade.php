@once
@push('styles')
<style>
    .clinical-phase-panel {
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
    }
    .clinical-phase-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--clinical-phase-bg, #f8f9fa);
        border-bottom: 3px solid var(--clinical-phase-color, #d1d5db);
    }
    .clinical-phase-panel__body {
        padding: 1rem;
    }
    .clinical-phase-badge {
        border: 1px solid rgba(0, 0, 0, 0.08);
        font-weight: 600;
    }
    .clinical-field-shell {
        background: var(--clinical-phase-bg, #f8f9fa);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 0.5rem;
        padding: 0.75rem;
        height: 100%;
    }
    .clinical-link {
        text-decoration: none;
    }
    .clinical-link:hover {
        text-decoration: underline;
    }
</style>
@endpush
@endonce
