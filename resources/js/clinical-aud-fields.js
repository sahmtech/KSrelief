function reindexAudMetrics(root) {
    const prefix = root.dataset.namePrefix;
    const body = root.querySelector('[data-aud-metrics-body]');
    if (!body || !prefix) return;

    body.querySelectorAll('[data-aud-metric-row]').forEach((row, index) => {
        const keyInput = row.querySelector('input[data-aud-key]');
        const valueInput = row.querySelector('input[data-aud-value]');
        if (keyInput) keyInput.name = `${prefix}[metrics][${index}][key]`;
        if (valueInput) valueInput.name = `${prefix}[metrics][${index}][value]`;
    });
}

function createAudMetricRow(root, index) {
    const prefix = root.dataset.namePrefix;
    const keyPh = root.querySelector('[data-aud-key]')?.placeholder || '';
    const valuePh = root.querySelector('[data-aud-value]')?.placeholder || '';
    const row = document.createElement('tr');
    row.setAttribute('data-aud-metric-row', '');
    row.innerHTML = `
        <td><input type="text" data-aud-key class="form-control form-control-sm" name="${prefix}[metrics][${index}][key]" placeholder="${keyPh}"></td>
        <td><input type="text" data-aud-value class="form-control form-control-sm" name="${prefix}[metrics][${index}][value]" placeholder="${valuePh}"></td>
        <td class="text-center align-middle"><button type="button" class="btn btn-outline-danger btn-sm" data-remove-aud-metric><i class="ti ti-trash"></i></button></td>
    `;
    return row;
}

function bindClinicalAudDelegation() {
    if (window.__clinicalAudFieldsBound) return;
    window.__clinicalAudFieldsBound = true;

    document.addEventListener('click', (event) => {
        const addBtn = event.target.closest('[data-add-aud-metric]');
        if (addBtn) {
            event.preventDefault();
            const root = addBtn.closest('[data-clinical-aud-root]');
            const body = root?.querySelector('[data-aud-metrics-body]');
            if (!root || !body) return;
            const index = body.querySelectorAll('[data-aud-metric-row]').length;
            body.appendChild(createAudMetricRow(root, index));
            return;
        }

        const removeBtn = event.target.closest('[data-remove-aud-metric]');
        if (removeBtn) {
            event.preventDefault();
            const row = removeBtn.closest('[data-aud-metric-row]');
            const root = removeBtn.closest('[data-clinical-aud-root]');
            if (!row || !root) return;
            row.remove();
            reindexAudMetrics(root);
        }
    });
}

export function initClinicalAudFields() {
    bindClinicalAudDelegation();
}

bindClinicalAudDelegation();
window.initClinicalAudFields = initClinicalAudFields;
