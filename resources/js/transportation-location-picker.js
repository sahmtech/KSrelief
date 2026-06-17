import {
    $,
    baseSelect2Options,
    destroySelect2,
    setPreselectedOption,
} from './select2-helpers';

function initTransportationLocationPicker(wrapper) {
    const select = wrapper.querySelector('[data-transportation-location-select]');
    const addBtn = wrapper.querySelector('[data-transportation-location-add]');
    const modalEl = wrapper.querySelector('[data-transportation-location-modal]');
    const nameInput = wrapper.querySelector('[data-transportation-location-name]');
    const typeSelect = wrapper.querySelector('[data-transportation-location-type]');
    const descriptionInput = wrapper.querySelector('[data-transportation-location-description]');
    const errorEl = wrapper.querySelector('[data-transportation-location-error]');
    const saveBtn = wrapper.querySelector('[data-transportation-location-save]');
    const canAdd = wrapper.dataset.canAdd === '1';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const modal = modalEl ? new window.bootstrap.Modal(modalEl) : null;

    const initSelect = async (locationId = '', locationLabel = '') => {
        destroySelect2(select);
        select.innerHTML = `<option value="">${wrapper.dataset.placeholder}</option>`;

        if (locationId && locationLabel) {
            setPreselectedOption(select, locationId, locationLabel);
        }

        $(select).select2({
            ...baseSelect2Options(wrapper, wrapper.dataset.placeholder, false),
            minimumInputLength: 0,
            ajax: {
                url: wrapper.dataset.urlSearch,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term || '',
                    limit: 50,
                }),
                processResults: (data) => ({
                    results: (data.data || []).map((row) => ({
                        id: row.id,
                        text: row.label,
                    })),
                }),
            },
        });

        if (locationId) {
            $(select).val(locationId).trigger('change');
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
        if (descriptionInput) {
            descriptionInput.value = '';
        }
        if (typeSelect) {
            typeSelect.value = '';
        }
        modal?.show();
    });

    saveBtn?.addEventListener('click', async () => {
        if (!nameInput?.value.trim() || !typeSelect?.value) {
            if (errorEl) {
                errorEl.textContent = wrapper.dataset.i18nRequired || 'Required';
                errorEl.classList.remove('d-none');
            }
            return;
        }

        const response = await fetch(wrapper.dataset.urlStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                name: nameInput.value.trim(),
                type: typeSelect.value,
                description: descriptionInput?.value.trim() || null,
            }),
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

        await initSelect(String(json.data.id), json.data.label);
        modal?.hide();
    });

    if (addBtn) {
        addBtn.classList.toggle('d-none', !canAdd);
    }

    initSelect(wrapper.dataset.selectedId || '', wrapper.dataset.selectedLabel || '');
}

export function initTransportationLocationPickers() {
    document.querySelectorAll('[data-transportation-location-picker]').forEach(initTransportationLocationPicker);
}
