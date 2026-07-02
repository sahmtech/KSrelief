import { setAudMetrics } from './clinical-aud-fields';

function findAudRoot(prefix) {
    return document.querySelector(`[data-clinical-aud-root][data-name-prefix="${prefix}"]`);
}

function ensureSelectValue(select, value, label) {
    if (!select) {
        return;
    }

    if (value == null || value === '') {
        select.value = '';
        return;
    }

    const stringValue = String(value);
    let option = select.querySelector(`option[value="${CSS.escape(stringValue)}"]`);

    if (!option) {
        option = document.createElement('option');
        option.value = stringValue;
        option.textContent = label || stringValue;
        select.appendChild(option);
    }

    select.value = stringValue;
}

async function populateElectrodes(select, url, companyId, selectedId = '') {
    if (!select || !url || !companyId) {
        if (select) {
            select.innerHTML = `<option value="">— ${select.dataset.emptyLabel || 'Select'} —</option>`;
        }
        return;
    }

    const response = await fetch(`${url}?implant_company_id=${encodeURIComponent(companyId)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });

    if (!response.ok) throw new Error('electrodes_failed');

    const payload = await response.json();
    select.innerHTML = `<option value="">— ${select.dataset.emptyLabel || 'Select'} —</option>`;
    (payload.data || []).forEach((item) => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.name;
        if (String(selectedId) === String(item.id)) {
            option.selected = true;
        }
        select.appendChild(option);
    });
}

export async function applyOperationTemplate(data, container = document.getElementById('stageFields')) {
    if (!container || !data) return;

    const setValue = (name, value) => {
        const el = container.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    };

    const labels = data._apply_labels || {};

    setValue('field_surgeon', data.surgeon);
    setValue('field_insertion_approach_id', data.insertion_approach_id);
    setValue('field_time_in_surgery', data.time_in_surgery);
    setValue('field_time_out_surgery', data.time_out_surgery);
    setValue('field_operation_notes', data.operation_notes);

    ensureSelectValue(
        container.querySelector('[name="field_intra_op_findings"]'),
        data.intra_op_findings,
        labels.intra_op_findings,
    );

    const depth = data.insertion_depth || {};
    const depthSelect = container.querySelector('[data-insertion-depth-select]');
    ensureSelectValue(depthSelect, depth.selection ?? '', labels.insertion_depth);
    setValue('field_insertion_depth[note]', depth.note ?? '');
    if (depthSelect) {
        depthSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const audioRoot = findAudRoot('field_audio_test');
    if (audioRoot) {
        setAudMetrics(audioRoot, data.audio_test?.metrics || []);
    }

    const companySelect = container.querySelector('#implantCompanySelect');
    const electrodeSelect = container.querySelector('#electrodeTypeSelect');
    const url = container.querySelector('[data-electrode-url]')?.dataset.electrodeUrl
        || companySelect?.dataset.electrodeUrl;

    if (companySelect && data.implant_company_id) {
        companySelect.value = data.implant_company_id;
        const color = companySelect.selectedOptions[0]?.dataset.color;
        companySelect.style.color = color || '';
        companySelect.style.fontWeight = companySelect.value ? '600' : '';
    }

    if (electrodeSelect && url && data.implant_company_id) {
        await populateElectrodes(electrodeSelect, url, data.implant_company_id, data.electrode_type_id);
    } else if (electrodeSelect) {
        setValue('field_electrode_type_id', data.electrode_type_id);
    }
}

async function loadCampaignOperationDefaults(config) {
    const container = document.getElementById('stageFields');
    const companySelect = container?.querySelector('#implantCompanySelect');
    let companyId = companySelect?.value;
    const { url, messages = {} } = config;

    if (!companyId && companySelect) {
        const options = Array.from(companySelect.options).filter((option) => option.value);

        if (options.length === 1) {
            companyId = options[0].value;
        } else if (options.length > 1 && window.Swal) {
            const inputOptions = Object.fromEntries(
                options.map((option) => [option.value, option.textContent.trim()])
            );
            const result = await window.Swal.fire({
                icon: 'question',
                title: messages.errorTitle || 'Notice',
                text: messages.selectCompany || 'Select an implant company first.',
                input: 'select',
                inputOptions,
                inputPlaceholder: messages.selectCompany || 'Select an implant company',
                showCancelButton: true,
                confirmButtonColor: '#0F766E',
            });

            if (!result.isConfirmed || !result.value) {
                return;
            }

            companyId = result.value;
        }
    }

    if (!companyId) {
        window.Swal?.fire({
            icon: 'info',
            title: messages.errorTitle || 'Notice',
            text: messages.selectCompany || 'Select an implant company first.',
            confirmButtonColor: '#0F766E',
        });
        return;
    }

    if (companySelect) {
        companySelect.value = companyId;
        const color = companySelect.selectedOptions[0]?.dataset.color;
        companySelect.style.color = color || '';
        companySelect.style.fontWeight = companySelect.value ? '600' : '';
        companySelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    try {
        const response = await fetch(`${url}?implant_company_id=${encodeURIComponent(companyId)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });

        if (!response.ok) throw new Error('load_failed');

        const payload = await response.json();
        if (!payload.has_defaults || !payload.data) {
            window.Swal?.fire({
                icon: 'info',
                title: messages.errorTitle || 'Notice',
                text: messages.noCampaignDefaults || 'No campaign defaults found for this company.',
                confirmButtonColor: '#0F766E',
            });
            return;
        }

        await applyOperationTemplate(payload.data);
        window.initOperationStageFields?.(container);

        window.Swal?.fire({
            icon: 'success',
            title: messages.successTitle || 'Success',
            text: messages.campaignDefaultsLoaded || 'Campaign defaults applied.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    } catch {
        window.Swal?.fire({
            icon: 'error',
            title: messages.errorTitle || 'Error',
            text: messages.loadFailed || 'Could not load campaign defaults.',
            confirmButtonColor: '#0F766E',
        });
    }
}

async function loadOperationTemplate(config) {
    const { url, hasDefaults, messages = {} } = config;

    if (!hasDefaults) {
        window.Swal?.fire({
            icon: 'info',
            title: messages.errorTitle || 'Notice',
            text: messages.noTemplate || 'No saved template found.',
            confirmButtonColor: '#0F766E',
        });
        return;
    }

    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });

        if (!response.ok) throw new Error('load_failed');

        const payload = await response.json();
        if (!payload.has_defaults || !payload.data) {
            window.Swal?.fire({
                icon: 'info',
                title: messages.errorTitle || 'Notice',
                text: messages.noTemplate || 'No saved template found.',
                confirmButtonColor: '#0F766E',
            });
            return;
        }

        await applyOperationTemplate(payload.data);
        window.initOperationStageFields?.(document.getElementById('stageFields'));

        window.Swal?.fire({
            icon: 'success',
            title: messages.successTitle || 'Success',
            text: messages.templateLoaded || 'Template applied.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    } catch {
        window.Swal?.fire({
            icon: 'error',
            title: messages.errorTitle || 'Error',
            text: messages.loadFailed || 'Could not load template.',
            confirmButtonColor: '#0F766E',
        });
    }
}

function bindOperationTemplateDelegation() {
    if (window.__operationTemplateDelegated) return;
    window.__operationTemplateDelegated = true;

    document.addEventListener('click', (event) => {
        const campaignBtn = event.target.closest('[data-operation-load-campaign-defaults]');
        if (campaignBtn && !campaignBtn.disabled && window.__campaignOperationDefaultsConfig) {
            event.preventDefault();
            loadCampaignOperationDefaults(window.__campaignOperationDefaultsConfig);
            return;
        }

        const btn = event.target.closest('[data-operation-load-template]');
        if (!btn || btn.disabled || !window.__operationTemplateConfig) return;

        event.preventDefault();
        loadOperationTemplate(window.__operationTemplateConfig);
    });
}

function syncOperationSaveDefaultVisibility(stageSelect, saveBtn, hint) {
    if (!stageSelect) return;

    const code = stageSelect.selectedOptions[0]?.dataset.code || '';
    const show = code === 'operation';

    if (saveBtn) saveBtn.hidden = !show;
    if (hint) hint.hidden = !show;
}

export function configureCampaignOperationDefaultsActions(options = {}) {
    window.__campaignOperationDefaultsConfig = options;
    bindOperationTemplateDelegation();
}

export function configureOperationTemplateActions(options = {}) {
    window.__operationTemplateConfig = options;
    bindOperationTemplateDelegation();

    const { saveBtn, hint, stageSelect } = options;
    if (!stageSelect) return;

    const sync = () => syncOperationSaveDefaultVisibility(stageSelect, saveBtn, hint);

    stageSelect.addEventListener('change', sync);
    sync();
}

window.applyOperationTemplate = applyOperationTemplate;
window.configureOperationTemplateActions = configureOperationTemplateActions;
window.configureCampaignOperationDefaultsActions = configureCampaignOperationDefaultsActions;
