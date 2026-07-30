import { setAudMetrics } from './clinical-aud-fields';

function findAudRoot(prefix) {
    return document.querySelector(`[data-clinical-aud-root][data-name-prefix="${prefix}"]`);
}

function setNamedValue(container, name, value) {
    const el = container.querySelector(`[name="${name}"]`);
    if (!el) return;

    if (el.type === 'checkbox') {
        el.checked = value === 'yes' || value === true || value === 1 || value === '1';
        return;
    }

    el.value = value ?? '';
}

export function applyPostOperationTemplate(data, container = document.getElementById('stageFields')) {
    if (!container || !data) return;

    const physician = data.physician_assessment || {};
    ['wound', 'implant_bed', 'facial_nerve', 'post_op_xray'].forEach((field) => {
        setNamedValue(container, `field_physician_assessment[${field}]`, physician[field] || '');
    });

    const audRoot = findAudRoot('field_clinical_aud');
    if (audRoot) {
        setAudMetrics(audRoot, data.clinical_aud?.metrics || []);
    }

    const counselling = container.querySelector('[name="field_counselling"][type="checkbox"]')
        || container.querySelector('[name="field_counselling"][value="yes"]');
    if (counselling) {
        counselling.checked = (data.counselling || 'no') === 'yes';
    }

    setNamedValue(container, 'field_post_op_notes', data.post_op_notes || '');
}

async function loadPostOperationTemplate(config) {
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
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
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

        applyPostOperationTemplate(payload.data);
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

function bindPostOperationTemplateDelegation() {
    if (window.__postOpTemplateDelegated) return;
    window.__postOpTemplateDelegated = true;

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-post-op-load-template]');
        if (!btn || btn.disabled || !window.__postOpTemplateConfig) return;

        event.preventDefault();
        loadPostOperationTemplate(window.__postOpTemplateConfig);
    });
}

function syncPostOpSaveDefaultVisibility(stageSelect, saveBtn, hint) {
    if (!stageSelect) return;

    const code = stageSelect.selectedOptions[0]?.dataset.code || '';
    const show = code === 'post_operation';

    if (saveBtn) saveBtn.hidden = !show;
    if (hint) hint.hidden = !show;
}

export function configurePostOperationTemplateActions(options = {}) {
    window.__postOpTemplateConfig = options;
    bindPostOperationTemplateDelegation();

    const { saveBtn, hint, stageSelect } = options;
    if (!stageSelect) return;

    const sync = () => syncPostOpSaveDefaultVisibility(stageSelect, saveBtn, hint);

    stageSelect.addEventListener('change', sync);
    sync();
}

window.applyPostOperationTemplate = applyPostOperationTemplate;
window.configurePostOperationTemplateActions = configurePostOperationTemplateActions;
