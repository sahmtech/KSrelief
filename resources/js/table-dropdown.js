import { Dropdown } from 'bootstrap';

function resolveDropdownToggle(el) {
    if (el.matches('button, [role="button"]')) {
        return el;
    }

    return el.querySelector('button[data-bs-toggle="dropdown"], button[data-table-dropdown]');
}

export function initTableDropdowns(root = document) {
    root.querySelectorAll('[data-table-dropdown]').forEach((el) => {
        const toggle = resolveDropdownToggle(el);

        if (! toggle || toggle.dataset.tableDropdownInit === '1') {
            return;
        }

        toggle.dataset.tableDropdownInit = '1';
        toggle.setAttribute('data-bs-toggle', 'dropdown');

        const menu = toggle.closest('.dropdown')?.querySelector('.dropdown-menu');
        const placement = menu?.classList.contains('dropdown-menu-end') ? 'bottom-end' : 'bottom-start';

        Dropdown.getOrCreateInstance(toggle, {
            popperConfig(defaultConfig) {
                return {
                    ...defaultConfig,
                    strategy: 'fixed',
                    placement: toggle.dataset.dropdownPlacement || placement,
                    modifiers: [
                        ...(defaultConfig.modifiers ?? []),
                        {
                            name: 'preventOverflow',
                            options: {
                                boundary: 'viewport',
                                padding: 8,
                                altAxis: true,
                            },
                        },
                        {
                            name: 'flip',
                            options: {
                                fallbackPlacements: ['top-end', 'bottom-end', 'top-start', 'bottom-start'],
                            },
                        },
                    ],
                };
            },
        });
    });
}

export function resetTableDropdowns(root = document) {
    root.querySelectorAll('[data-table-dropdown]').forEach((el) => {
        const toggle = resolveDropdownToggle(el);

        if (! toggle) {
            return;
        }

        const instance = Dropdown.getInstance(toggle);
        instance?.dispose();
        delete toggle.dataset.tableDropdownInit;
    });
}
