import { setAudMetrics } from './clinical-aud-fields';
import { applyImagingFindingsData } from './imaging-findings-tree';
import { applyHearingAssessmentData } from './hearing-assessment';

function findAudRoot(prefix) {
    return document.querySelector(`[data-clinical-aud-root][data-name-prefix="${prefix}"]`);
}

function setNamedValue(container, name, value) {
    const el = container.querySelector(`[name="${name}"]`);
    if (!el) return;

    if (el.tagName === 'SELECT' || el.tagName === 'TEXTAREA' || el.tagName === 'INPUT') {
        if (el.type === 'checkbox') {
            el.checked = value === 'yes' || value === true || value === 1 || value === '1';
            return;
        }
        el.value = value ?? '';
    }
}

export function applyPreOperationTemplate(data, container = document.getElementById('stageFields')) {
    if (!container || !data) return;

    const physician = data.physician_assessment || {};
    ['general_condition', 'pre_op_request', 'clinical_decision'].forEach((field) => {
        setNamedValue(container, `field_physician_assessment[${field}]`, physician[field] || '');
    });
    setNamedValue(container, 'field_physician_assessment[notes]', physician.notes || '');

    const imagingRoot = container.querySelector('[data-imaging-findings-tree][data-name-prefix="field_imaging_findings"]')
        || container.querySelector('[data-imaging-findings-tree]');
    if (imagingRoot && data.imaging_findings) {
        applyImagingFindingsData(imagingRoot, data.imaging_findings);
    }

    const audiology = data.audiology_decision || {};
    setNamedValue(container, 'field_audiology_decision[status]', audiology.status || '');
    setNamedValue(container, 'field_audiology_decision[decision]', audiology.decision || '');
    setNamedValue(container, 'field_audiology_decision[audiology_link]', audiology.audiology_link || '');

    const audiologyRoot = findAudRoot('field_audiology_decision');
    if (audiologyRoot) {
        setAudMetrics(audiologyRoot, audiology.metrics || []);
    }

    const hearingRoot = container.querySelector('.pre-op-audiology-decision [data-hearing-assessment-root]');
    if (hearingRoot) {
        applyHearingAssessmentData(hearingRoot, audiology, 'field_audiology_decision');
    }

    const speech = data.speech_assessment || {};
    [
        'communication_mood',
        'iq',
        'cognitive_function',
        'true_word',
        'phrases',
        'expectations_post_ci',
        'assessment',
        'speech_decision',
        'cap',
        'sir',
        'notes',
    ].forEach((field) => {
        setNamedValue(container, `field_speech_assessment[${field}]`, speech[field] || '');
    });
}

async function loadPreOperationTemplate(config) {
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

        applyPreOperationTemplate(payload.data);
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

function bindPreOperationTemplateDelegation() {
    if (window.__preOpTemplateDelegated) return;
    window.__preOpTemplateDelegated = true;

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-pre-op-load-template]');
        if (!btn || btn.disabled || !window.__preOpTemplateConfig) return;

        event.preventDefault();
        loadPreOperationTemplate(window.__preOpTemplateConfig);
    });
}

function syncPreOpSaveDefaultVisibility(stageSelect, saveBtn, hint) {
    if (!stageSelect) return;

    const code = stageSelect.selectedOptions[0]?.dataset.code || '';
    const show = code === 'pre_operation';

    if (saveBtn) saveBtn.hidden = !show;
    if (hint) hint.hidden = !show;
}

export function configurePreOperationTemplateActions(options = {}) {
    window.__preOpTemplateConfig = options;
    bindPreOperationTemplateDelegation();

    const { saveBtn, hint, stageSelect } = options;
    if (!stageSelect) return;

    const sync = () => syncPreOpSaveDefaultVisibility(stageSelect, saveBtn, hint);

    stageSelect.addEventListener('change', sync);
    sync();
}

window.applyPreOperationTemplate = applyPreOperationTemplate;
window.configurePreOperationTemplateActions = configurePreOperationTemplateActions;
