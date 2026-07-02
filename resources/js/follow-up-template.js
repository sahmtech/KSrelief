import { setAudMetrics } from './clinical-aud-fields';

function findAudRoot(prefix) {
    return document.querySelector(`[data-clinical-aud-root][data-name-prefix="${prefix}"]`);
}

export function applyFollowUpTemplate(data, container = document.getElementById('stageFields')) {
    if (!container || !data) return;

    const clinical = data.clinical_assessment || {};

    ['wound', 'implant_bed'].forEach((field) => {
        const select = container.querySelector(`select[name="field_clinical_assessment[${field}]"]`);
        if (select) {
            select.value = clinical[field] || '';
        }
    });

    const audiologyRoot = findAudRoot('field_audiology_assessment');
    if (audiologyRoot) {
        setAudMetrics(audiologyRoot, data.audiology_assessment?.metrics || []);
    }

    const speech = data.speech_assessment || {};
    ['communication_mood', 'true_word', 'phrases'].forEach((field) => {
        const select = container.querySelector(`select[name="field_speech_assessment[${field}]"]`);
        if (select) {
            select.value = speech[field] || '';
        }
    });

    const notes = container.querySelector('[name="field_follow_up_notes"]');
    if (notes) {
        notes.value = data.follow_up_notes || '';
    }
}

async function loadFollowUpTemplate(config) {
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

        applyFollowUpTemplate(payload.data);
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

function bindFollowUpTemplateDelegation() {
    if (window.__followUpTemplateDelegated) return;
    window.__followUpTemplateDelegated = true;

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-follow-up-load-template]');
        if (!btn || btn.disabled || !window.__followUpTemplateConfig) return;

        event.preventDefault();
        loadFollowUpTemplate(window.__followUpTemplateConfig);
    });
}

function syncFollowUpSaveDefaultVisibility(stageSelect, saveBtn, hint) {
    if (!stageSelect) return;

    const code = stageSelect.selectedOptions[0]?.dataset.code || '';
    const show = code === 'follow_up';

    if (saveBtn) saveBtn.hidden = !show;
    if (hint) hint.hidden = !show;
}

export function configureFollowUpTemplateActions(options = {}) {
    window.__followUpTemplateConfig = options;
    bindFollowUpTemplateDelegation();

    const { saveBtn, hint, stageSelect } = options;
    if (!stageSelect) return;

    const sync = () => syncFollowUpSaveDefaultVisibility(stageSelect, saveBtn, hint);

    stageSelect.addEventListener('change', sync);
    sync();
}

window.applyFollowUpTemplate = applyFollowUpTemplate;
window.configureFollowUpTemplateActions = configureFollowUpTemplateActions;
window.initFollowUpTemplateActions = configureFollowUpTemplateActions;
