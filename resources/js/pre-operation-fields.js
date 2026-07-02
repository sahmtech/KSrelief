function appendSelectOption(select, value, label, selected = true) {
    if (!select || select.querySelector(`option[value="${CSS.escape(String(value))}"]`)) {
        return;
    }

    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    if (selected) {
        option.selected = true;
    }
    select.appendChild(option);

    if (selected) {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function appendImagingCheckbox(body, type, ear, namePrefix, id, label, checked = true) {
    if (!body) return;

    const inputId = `${namePrefix}_${ear}_${type}_${id}`;
    if (document.getElementById(inputId)) {
        const existing = document.getElementById(inputId);
        if (checked) existing.checked = true;
        return;
    }

    const wrap = document.createElement('div');
    wrap.className = 'form-check';
    wrap.innerHTML = `
        <input class="form-check-input" type="checkbox" name="${namePrefix}[${ear}][${type}][]" id="${inputId}" value="${id}" ${checked ? 'checked' : ''}>
        <label class="form-check-label small" for="${inputId}">${label}</label>
    `;
    body.appendChild(wrap);
}

async function postJson(url, payload) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token || '',
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error('request_failed');
    }

    return response.json();
}

function bindPreOperationFields() {
    if (window.__preOperationFieldsBound) return;
    window.__preOperationFieldsBound = true;

    document.addEventListener('click', async (event) => {
        const inlineBtn = event.target.closest('[data-add-inline-option]');
        if (inlineBtn) {
            event.preventDefault();
            const root = inlineBtn.closest('[data-inline-select]');
            const input = root?.querySelector('[data-inline-option-input]');
            const select = root?.querySelector('[data-inline-select-control]');
            const url = root?.dataset.addUrl;
            const category = root?.dataset.category;
            const name = input?.value?.trim();

            if (!root || !select || !url || !category || !name) return;

            try {
                const data = await postJson(url, { category, name });
                appendSelectOption(select, data.code ?? data.id, data.label, true);
                input.value = '';
            } catch {
                window.Swal?.fire({
                    icon: 'error',
                    title: document.body.dataset.i18nError || 'Error',
                    text: document.body.dataset.preOpOptionFailed || 'Could not save option.',
                    confirmButtonColor: '#0F766E',
                });
            }
            return;
        }

        const imagingBtn = event.target.closest('[data-add-imaging-option]');
        if (imagingBtn) {
            event.preventDefault();
            const list = imagingBtn.closest('[data-imaging-option-list]');
            const input = list?.querySelector('[data-imaging-option-input]');
            const body = list?.querySelector('[data-imaging-options-body]');
            const url = imagingBtn.dataset.addUrl;
            const type = list?.dataset.imagingType;
            const ear = list?.dataset.ear;
            const namePrefix = list.closest('[data-name-prefix]')?.dataset.namePrefix || 'field_imaging_findings';
            const name = input?.value?.trim();

            if (!list || !body || !url || !type || !ear || !name) return;

            try {
                const data = await postJson(url, { name });
                appendImagingCheckbox(body, type, ear, namePrefix, data.id, data.label, true);
                input.value = '';
            } catch {
                window.Swal?.fire({
                    icon: 'error',
                    title: document.body.dataset.i18nError || 'Error',
                    text: document.body.dataset.preOpOptionFailed || 'Could not save option.',
                    confirmButtonColor: '#0F766E',
                });
            }
        }
    });
}

bindPreOperationFields();
window.bindPreOperationFields = bindPreOperationFields;
