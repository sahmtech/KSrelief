import {
    $,
    baseSelect2Options,
    destroySelect2,
    resolveLabel,
    setPreselectedOption,
} from './select2-helpers';

function initSpecialtyPicker(wrapper) {
    const select = wrapper.querySelector('[data-specialty-select]');
    const addBtn = wrapper.querySelector('[data-specialty-add]');
    const modalEl = wrapper.querySelector('[data-specialty-modal]');
    const nameInput = wrapper.querySelector('[data-specialty-new-name]');
    const errorEl = wrapper.querySelector('[data-specialty-error]');
    const saveBtn = wrapper.querySelector('[data-specialty-save]');
    const canAdd = wrapper.dataset.canAddSpecialty === '1';
    const required = wrapper.dataset.required === '1';
    const selectedId = wrapper.dataset.selectedSpecialty || '';
    const selectedName = wrapper.dataset.selectedSpecialtyName || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const modal = modalEl ? new window.bootstrap.Modal(modalEl) : null;

    const initSelect = async () => {
        destroySelect2(select);
        select.innerHTML = `<option value="">${wrapper.dataset.placeholder}</option>`;

        const resolvedName = selectedName || (selectedId
            ? await resolveLabel(wrapper.dataset.urlSpecialties, selectedId)
            : '');

        if (selectedId && resolvedName) {
            setPreselectedOption(select, selectedId, resolvedName);
        }

        $(select).select2({
            ...baseSelect2Options(wrapper, wrapper.dataset.placeholder, !required),
            minimumInputLength: 0,
            ajax: {
                url: wrapper.dataset.urlSpecialties,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term || '',
                    limit: 50,
                }),
                processResults: (data) => ({
                    results: (data.data || []).map((specialty) => ({
                        id: specialty.id,
                        text: specialty.name,
                    })),
                }),
            },
        });

        if (selectedId) {
            $(select).val(selectedId).trigger('change');
        }
    };

    addBtn?.addEventListener('click', () => {
        if (errorEl) {
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
        }
        if (nameInput) {
            nameInput.value = '';
        }
        modal?.show();
    });

    saveBtn?.addEventListener('click', async () => {
        if (!nameInput?.value.trim()) {
            return;
        }

        const response = await fetch(wrapper.dataset.urlSpecialties, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ name: nameInput.value.trim() }),
        });

        const json = await response.json();

        if (!response.ok) {
            if (errorEl) {
                const firstError = json.errors ? Object.values(json.errors)[0]?.[0] : null;
                errorEl.textContent = firstError || json.message || 'Error';
                errorEl.classList.remove('d-none');
            }
            return;
        }

        await initSelect();
        $(select).val(String(json.data.id)).trigger('change');
        modal?.hide();
    });

    if (addBtn) {
        addBtn.disabled = !canAdd;
    }

    initSelect();
}

export function initSpecialtyPickers() {
    document.querySelectorAll('[data-specialty-picker]').forEach(initSpecialtyPicker);
}
