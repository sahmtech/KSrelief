export function syncInsertionDepthNote(select) {
    const root = select.closest('[data-operation-insertion-depth]');
    const noteWrap = root?.querySelector('[data-insertion-depth-note]');
    const isPartial = select.value === 'partial_insertion';

    if (!noteWrap) {
        return;
    }

    noteWrap.hidden = !isPartial;
}

export function initOperationInsertionDepth(root = document) {
    root.querySelectorAll('[data-insertion-depth-select]').forEach(syncInsertionDepthNote);
}

if (!window.__insertionDepthDelegated) {
    window.__insertionDepthDelegated = true;

    document.addEventListener('change', (event) => {
        const select = event.target.closest('[data-insertion-depth-select]');
        if (select) {
            syncInsertionDepthNote(select);
        }
    });
}

window.initOperationInsertionDepth = initOperationInsertionDepth;
