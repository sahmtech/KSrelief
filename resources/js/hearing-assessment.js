import {
    $,
    baseSelect2Options,
    destroySelect2,
} from './select2-helpers';

function syncHearingTypePanels(root, select) {
    const selected = new Set($(select).val() || []);

    root.querySelectorAll('[data-hearing-type-panel]').forEach((panel) => {
        const type = panel.dataset.hearingTypePanel;
        panel.classList.toggle('d-none', !selected.has(type));
    });
}

function initHearingTypeSelect2(root) {
    const select = root.querySelector('[data-hearing-type-select2]');
    if (!select) {
        return;
    }

    const wrapper = select.closest('[data-hearing-type-select]') || root;

    destroySelect2(select);

    $(select).select2({
        ...baseSelect2Options(wrapper, wrapper.dataset.placeholder || '', true),
        multiple: true,
        closeOnSelect: false,
    });

    $(select).off('change.hearingAssessment').on('change.hearingAssessment', () => {
        syncHearingTypePanels(root, select);
    });

    syncHearingTypePanels(root, select);
}

function initHearingAssessmentWorkflow(scope = document) {
    const roots = scope.querySelectorAll
        ? scope.querySelectorAll('[data-hearing-assessment-root]')
        : [];

    roots.forEach((root) => {
        initHearingTypeSelect2(root);
    });
}

export function applyHearingAssessmentData(root, data, namePrefix) {
    if (!root || !data) {
        return;
    }

    const types = Array.isArray(data.hearing_types) ? data.hearing_types.map(String) : [];
    const select = root.querySelector('[data-hearing-type-select2]');
    if (select) {
        $(select).val(types).trigger('change');
        syncHearingTypePanels(root, select);
    }

    const assessments = data.assessments || {};
    Object.entries(assessments).forEach(([type, assessment]) => {
        if (!assessment || typeof assessment !== 'object') {
            return;
        }

        ['right', 'left'].forEach((ear) => {
            const value = assessment[ear];
            if (value == null || value === '') {
                return;
            }
            const radio = root.querySelector(
                `input[type="radio"][name="${namePrefix}[assessments][${type}][${ear}]"][value="${CSS.escape(String(Array.isArray(value) ? value[0] : value))}"]`
            );
            if (radio) {
                radio.checked = true;
            }
        });

        Object.entries(assessment).forEach(([sectionKey, sectionValue]) => {
            if (sectionKey === 'right' || sectionKey === 'left' || !sectionValue || typeof sectionValue !== 'object') {
                return;
            }

            Object.entries(sectionValue).forEach(([frequencyOrEar, freqValue]) => {
                if (freqValue && typeof freqValue === 'object' && !Array.isArray(freqValue)) {
                    Object.entries(freqValue).forEach(([ear, cellValue]) => {
                        const input = root.querySelector(
                            `input[name="${namePrefix}[assessments][${type}][${sectionKey}][${frequencyOrEar}][${ear}]"]`
                        );
                        if (input) {
                            input.value = cellValue ?? '';
                        }
                    });
                    return;
                }

                const input = root.querySelector(
                    `input[name="${namePrefix}[assessments][${type}][${sectionKey}][${frequencyOrEar}]"]`
                );
                if (input) {
                    input.value = freqValue ?? '';
                }
            });
        });
    });
}

initHearingAssessmentWorkflow();
document.addEventListener('DOMContentLoaded', () => initHearingAssessmentWorkflow());
document.addEventListener('turbo:load', () => initHearingAssessmentWorkflow());

window.initHearingAssessmentWorkflow = initHearingAssessmentWorkflow;
window.applyHearingAssessmentData = applyHearingAssessmentData;

export { initHearingAssessmentWorkflow };
