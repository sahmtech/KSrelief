function setCheckboxState(checkbox, state) {
    if (!checkbox) {
        return;
    }

    checkbox.indeterminate = state === 'indeterminate';
    checkbox.checked = state === 'checked';
    checkbox.dataset.indeterminate = state === 'indeterminate' ? '1' : '0';
}

function getLeafCheckboxes(panel) {
    return [...panel.querySelectorAll('[data-imaging-tree-checkbox][name]')];
}

function getParentCheckboxes(panel) {
    return [...panel.querySelectorAll('[data-imaging-tree-checkbox][data-has-children="1"]')];
}

function getDescendantLeaves(panel, path) {
    return getLeafCheckboxes(panel).filter((checkbox) => {
        const leafPath = checkbox.dataset.path || '';
        return leafPath === path || leafPath.startsWith(`${path}.`);
    });
}

function syncParentStates(panel) {
    getParentCheckboxes(panel).forEach((parent) => {
        const path = parent.dataset.path || '';
        const leaves = getDescendantLeaves(panel, path);
        if (leaves.length === 0) {
            return;
        }

        const checkedCount = leaves.filter((leaf) => leaf.checked).length;
        if (checkedCount === 0) {
            setCheckboxState(parent, 'unchecked');
        } else if (checkedCount === leaves.length) {
            setCheckboxState(parent, 'checked');
        } else {
            setCheckboxState(parent, 'indeterminate');
        }
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function selectedLabelsForPanel(panel) {
    return getLeafCheckboxes(panel)
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => {
            if (checkbox.dataset.breadcrumb) {
                return checkbox.dataset.breadcrumb;
            }

            const label = panel.querySelector(`label[for="${checkbox.id}"]`);
            return label?.textContent?.trim() || checkbox.dataset.label || checkbox.value;
        });
}

function updateOverview(root) {
    const overview = root.querySelector('[data-imaging-tree-overview]');
    const body = overview?.querySelector('[data-imaging-tree-overview-body]');
    const emptyState = overview?.querySelector('[data-imaging-tree-overview-empty]');
    if (!overview || !body || !emptyState) {
        return;
    }

    const cards = [];

    root.querySelectorAll('[data-imaging-ear-panel]').forEach((panel) => {
        const items = selectedLabelsForPanel(panel);
        if (items.length === 0) {
            return;
        }

        const modality = panel.dataset.imagingModality || '';
        const ear = panel.dataset.imagingEarPanel || '';
        const title = panel.dataset.summaryTitle
            || `${String(modality).toUpperCase()} — ${ear}`;
        const icon = modality === 'mri' ? 'ti-brain' : 'ti-scan';

        cards.push(`
            <div class="col-md-6">
                <div class="imaging-tree-overview__card">
                    <div class="imaging-tree-overview__card-title">
                        <i class="ti ${icon} me-1"></i>${escapeHtml(title)}
                    </div>
                    <ul class="imaging-tree-overview__list">
                        ${items.map((label) => `<li>${escapeHtml(label)}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `);
    });

    body.innerHTML = cards.join('');
    emptyState.classList.toggle('d-none', cards.length > 0);
    body.classList.toggle('d-none', cards.length === 0);
    overview.classList.toggle('has-selection', cards.length > 0);
}

function syncPanel(panel, root) {
    syncParentStates(panel);
    if (root) {
        updateOverview(root);
    }
}

function bindModalityTabs(root) {
    root.querySelectorAll('[data-imaging-modality-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            const modality = button.dataset.imagingModalityTab;
            root.querySelectorAll('[data-imaging-modality-tab]').forEach((tab) => {
                tab.classList.toggle('active', tab === button);
                tab.setAttribute('aria-selected', tab === button ? 'true' : 'false');
            });
            root.querySelectorAll('[data-imaging-modality-panel]').forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.imagingModalityPanel !== modality);
            });
        });
    });
}

function bindEarTabs(root) {
    root.querySelectorAll('[data-imaging-ear-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            const ear = button.dataset.imagingEarTab;
            const modality = button.dataset.imagingModality;
            const modalityPanel = root.querySelector(`[data-imaging-modality-panel="${modality}"]`);
            if (!modalityPanel) {
                return;
            }

            modalityPanel.querySelectorAll(`[data-imaging-ear-tab][data-imaging-modality="${modality}"]`).forEach((tab) => {
                tab.classList.toggle('active', tab === button);
                tab.setAttribute('aria-selected', tab === button ? 'true' : 'false');
            });

            modalityPanel.querySelectorAll(`[data-imaging-ear-panel][data-imaging-modality="${modality}"]`).forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.imagingEarPanel !== ear);
            });
        });
    });
}

function bindTreeToggles(root) {
    root.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-imaging-tree-toggle]');
        if (!toggle) {
            return;
        }

        event.preventDefault();
        const node = toggle.closest('[data-imaging-tree-node]');
        const children = node?.querySelector(':scope > [data-imaging-tree-children]');
        if (!children) {
            return;
        }

        const expanded = toggle.getAttribute('aria-expanded') !== 'false';
        toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        children.classList.toggle('d-none', expanded);
        toggle.querySelector('i')?.classList.toggle('ti-chevron-down', !expanded);
        toggle.querySelector('i')?.classList.toggle('ti-chevron-right', expanded);
    });
}

function bindExpandCollapse(root) {
    root.addEventListener('click', (event) => {
        const expandAll = event.target.closest('[data-imaging-tree-expand-all]');
        const collapseAll = event.target.closest('[data-imaging-tree-collapse-all]');
        const panel = (expandAll || collapseAll)?.closest('[data-imaging-ear-panel]');
        if (!panel) {
            return;
        }

        panel.querySelectorAll('[data-imaging-tree-toggle]').forEach((toggle) => {
            const expanded = Boolean(expandAll);
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            const children = toggle.closest('[data-imaging-tree-node]')?.querySelector(':scope > [data-imaging-tree-children]');
            children?.classList.toggle('d-none', !expanded);
            toggle.querySelector('i')?.classList.toggle('ti-chevron-down', expanded);
            toggle.querySelector('i')?.classList.toggle('ti-chevron-right', !expanded);
        });
    });
}

function bindCheckboxCascade(root) {
    root.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-imaging-tree-checkbox]');
        if (!checkbox) {
            return;
        }

        const panel = checkbox.closest('[data-imaging-ear-panel]');
        if (!panel) {
            return;
        }

        const path = checkbox.dataset.path || '';
        const hasChildren = checkbox.dataset.hasChildren === '1';
        const shouldCheck = checkbox.checked;

        if (hasChildren) {
            getDescendantLeaves(panel, path).forEach((leaf) => {
                leaf.checked = shouldCheck;
            });
        }

        syncPanel(panel, root);
    });
}

function initializeTree(root) {
    root.querySelectorAll('[data-imaging-ear-panel]').forEach((panel) => {
        panel.querySelectorAll('[data-imaging-tree-checkbox][data-indeterminate="1"]').forEach((checkbox) => {
            setCheckboxState(checkbox, 'indeterminate');
        });
        syncParentStates(panel);
    });
    updateOverview(root);
}

export function applyImagingFindingsData(root, data) {
    if (!root || !data) {
        return;
    }

    ['right', 'left'].forEach((ear) => {
        const earData = data[ear] || {};

        ['ct', 'mri'].forEach((modality) => {
            const panel = root.querySelector(
                `[data-imaging-ear-panel="${ear}"][data-imaging-modality="${modality}"]`
            );
            if (!panel) {
                return;
            }

            const selected = new Set(
                (earData[modality] || []).map((value) => String(value))
            );

            getLeafCheckboxes(panel).forEach((checkbox) => {
                checkbox.checked = selected.has(String(checkbox.value));
                checkbox.indeterminate = false;
                checkbox.dataset.indeterminate = '0';
            });

            const notesField = modality === 'ct' ? 'ct_notes' : 'mri_notes';
            const driveField = modality === 'ct' ? 'ct_drive_link' : 'mri_drive_link';
            const notesInput = panel.querySelector(`textarea[name$="[${notesField}]"]`);
            const driveInput = panel.querySelector(`input[name$="[${driveField}]"]`);
            if (notesInput) notesInput.value = earData[notesField] || '';
            if (driveInput) driveInput.value = earData[driveField] || '';

            syncParentStates(panel);
        });
    });

    updateOverview(root);
}

function initImagingFindingsTrees(scope = document) {
    const roots = scope.querySelectorAll
        ? scope.querySelectorAll('[data-imaging-findings-tree]')
        : document.querySelectorAll('[data-imaging-findings-tree]');

    roots.forEach((root) => {
        if (root.dataset.imagingTreeBound === '1') {
            return;
        }
        root.dataset.imagingTreeBound = '1';

        bindModalityTabs(root);
        bindEarTabs(root);
        bindTreeToggles(root);
        bindExpandCollapse(root);
        bindCheckboxCascade(root);
        initializeTree(root);
    });
}

initImagingFindingsTrees();
document.addEventListener('turbo:load', () => initImagingFindingsTrees());
document.addEventListener('DOMContentLoaded', () => initImagingFindingsTrees());

window.initImagingFindingsTrees = initImagingFindingsTrees;
window.applyImagingFindingsData = applyImagingFindingsData;

export { initImagingFindingsTrees };
