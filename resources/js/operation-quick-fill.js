import { applyOperationTemplate } from './operation-template';

const SUMMARY_KEYS = [
    'electrode',
    'insertion_approach',
    'insertion_depth',
    'intra_op_findings',
];

function getMessages(root) {
    return {
        applied: root?.dataset.msgApplied || 'Form filled.',
        loadFailed: root?.dataset.msgLoadFailed || 'Could not load presets.',
        successTitle: root?.dataset.msgSuccess || 'Success',
        errorTitle: root?.dataset.msgError || 'Error',
        applyLabel: root?.dataset.msgApply || 'Apply',
        myTemplate: root?.dataset.msgMyTemplate || 'My Template',
        campaignDefault: root?.dataset.msgCampaignDefault || 'Campaign Defaults',
        notSet: root?.dataset.summaryEmpty || '—',
    };
}

function getSummaryLabels(root) {
    return {
        electrode: root?.dataset.labelElectrode || 'Electrode',
        insertion_approach: root?.dataset.labelInsertionApproach || 'Insertion approach',
        insertion_depth: root?.dataset.labelInsertionDepth || 'Insertion depth',
        intra_op_findings: root?.dataset.labelIntraOp || 'Intra-op findings',
    };
}

function renderPresetCard(preset, labels, messages) {
    const card = document.createElement('button');
    card.type = 'button';
    card.className = 'operation-quick-fill__preset-card';
    card.dataset.presetId = preset.id;
    card.style.setProperty('--company-color', preset.company?.color || '#0F766E');

    const summaryItems = SUMMARY_KEYS
        .map((key) => {
            const value = preset.summary?.[key] || messages.notSet;
            return `<li><span>${labels[key]}</span><strong>${value}</strong></li>`;
        })
        .join('');

    const badge = preset.source === 'user_default'
        ? `<span class="operation-quick-fill__preset-badge operation-quick-fill__preset-badge--user">${messages.myTemplate}</span>`
        : preset.source === 'campaign_default'
            ? `<span class="operation-quick-fill__preset-badge operation-quick-fill__preset-badge--campaign">${messages.campaignDefault}</span>`
            : '';

    card.innerHTML = `
        <div class="operation-quick-fill__preset-head">
            <strong>${preset.name}</strong>
            ${badge}
        </div>
        <ul class="operation-quick-fill__preset-summary">${summaryItems}</ul>
        <span class="operation-quick-fill__preset-action">
            <i class="ti ti-click me-1"></i>${messages.applyLabel}
        </span>
    `;

    card.__payload = preset.payload;
    return card;
}

async function loadPresets(root, companyId, companyName) {
    const url = root.dataset.url;
    const companiesStep = root.querySelector('[data-quick-fill-companies-step]');
    const presetsStep = root.querySelector('[data-quick-fill-presets-step]');
    const loading = root.querySelector('[data-quick-fill-loading]');
    const grid = root.querySelector('[data-quick-fill-grid]');
    const empty = root.querySelector('[data-quick-fill-empty]');
    const title = root.querySelector('[data-quick-fill-company-title]');
    const messages = getMessages(root);
    const labels = getSummaryLabels(root);

    companiesStep.hidden = true;
    presetsStep.hidden = false;
    loading.hidden = false;
    grid.innerHTML = '';
    empty.hidden = true;
    title.textContent = companyName;

    try {
        const response = await fetch(`${url}?implant_company_id=${encodeURIComponent(companyId)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });

        if (!response.ok) throw new Error('load_failed');

        const payload = await response.json();
        const presets = payload.presets || [];

        if (presets.length === 0) {
            empty.hidden = false;
        } else {
            presets.forEach((preset) => {
                const card = renderPresetCard(preset, labels, messages);
                card.addEventListener('click', () => applyPreset(root, card.__payload, messages));
                grid.appendChild(card);
            });
        }
    } catch {
        empty.hidden = false;
        empty.textContent = messages.loadFailed;
    } finally {
        loading.hidden = true;
    }
}

async function applyPreset(root, payload, messages) {
    const container = document.getElementById('stageFields');
    await applyOperationTemplate(payload, container);
    window.initOperationStageFields?.(container);
    switchOperationTab('manual');

    window.Swal?.fire({
        icon: 'success',
        title: messages.successTitle,
        text: messages.applied,
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
    });
}

export function switchOperationTab(tab) {
    const scope = document.querySelector('.operation-stage-fields');
    if (!scope) return;

    scope.querySelectorAll('[data-operation-tab]').forEach((btn) => {
        const active = btn.dataset.operationTab === tab;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    scope.querySelectorAll('[data-operation-tab-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.operationTabPanel !== tab;
    });
}

export function initOperationQuickFill(root = document) {
    const scope = root.querySelector?.('.operation-stage-fields')
        || (root.matches?.('.operation-stage-fields') ? root : null);

    if (!scope || scope.dataset.quickFillBound === '1') {
        return;
    }

    scope.dataset.quickFillBound = '1';

    scope.querySelectorAll('[data-operation-tab]').forEach((btn) => {
        btn.addEventListener('click', () => switchOperationTab(btn.dataset.operationTab));
    });

    const quickFillRoot = scope.querySelector('[data-operation-quick-fill]');
    if (!quickFillRoot) return;

    quickFillRoot.querySelectorAll('.operation-quick-fill__company-card').forEach((btn) => {
        btn.addEventListener('click', () => {
            loadPresets(
                quickFillRoot,
                btn.dataset.companyId,
                btn.querySelector('.operation-quick-fill__company-name')?.textContent?.trim() || '',
            );
        });
    });

    quickFillRoot.querySelector('[data-quick-fill-back]')?.addEventListener('click', () => {
        quickFillRoot.querySelector('[data-quick-fill-companies-step]').hidden = false;
        quickFillRoot.querySelector('[data-quick-fill-presets-step]').hidden = true;
    });
}

window.initOperationQuickFill = initOperationQuickFill;
window.switchOperationTab = switchOperationTab;

document.addEventListener('DOMContentLoaded', () => {
    initOperationQuickFill(document.getElementById('stageFields'));
});
