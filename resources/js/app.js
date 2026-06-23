import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import $ from 'jquery';
window.$ = window.jQuery = $;

import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5';

import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

import Swal from 'sweetalert2';
window.Swal = Swal;

import { initLocationPickers } from './location-picker';
import { initTransportationLocationPickers } from './transportation-location-picker';
import { initSpecialtyPickers } from './specialty-picker';
import { initActivityParticipantMultiselects } from './activity-participants';
import { initTableDropdowns, resetTableDropdowns } from './table-dropdown';
import { initPatientNavbarSearch } from './patient-search';
import './clinical-aud-fields';

function getBodyI18n() {
    const body = document.body;
    const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

    return {
        locale: body.dataset.locale || 'en',
        isRtl,
        success: body.dataset.i18nSuccess || 'Success',
        error: body.dataset.i18nError || 'Error',
        confirmTitle: body.dataset.i18nConfirmTitle || 'Confirm',
        confirmMessage: body.dataset.i18nConfirmMessage || 'Are you sure?',
        confirmYes: body.dataset.i18nConfirmYes || 'Yes',
        cancel: body.dataset.i18nCancel || 'Cancel',
        datatable: {
            search: body.dataset.dtSearch || '',
            searchPlaceholder: body.dataset.dtSearchPlaceholder || 'Search...',
            lengthMenu: body.dataset.dtLengthMenu || 'Show _MENU_ entries',
            info: body.dataset.dtInfo || 'Showing _START_ to _END_ of _TOTAL_ entries',
            emptyTable: body.dataset.dtEmpty || 'No data',
            zeroRecords: body.dataset.dtZero || 'No matching records',
        },
    };
}

function stripColspanPlaceholderRows(table) {
    table.querySelectorAll('tbody tr').forEach((row) => {
        const cells = row.cells;

        if (cells.length === 1 && cells[0].colSpan > 1) {
            row.remove();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const i18n = getBodyI18n();
    const chevronPrev = i18n.isRtl ? 'ti-chevron-right' : 'ti-chevron-left';
    const chevronNext = i18n.isRtl ? 'ti-chevron-left' : 'ti-chevron-right';

    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebarStorageKey = 'admin-sidebar-collapsed';
    const desktopSidebarQuery = window.matchMedia('(min-width: 992px)');

    const isDesktopSidebar = () => desktopSidebarQuery.matches;

    const closeMobileSidebar = () => {
        if (! sidebar || ! overlay) {
            return;
        }

        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    };

    const openMobileSidebar = () => {
        if (! sidebar || ! overlay) {
            return;
        }

        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    const applyDesktopSidebarState = () => {
        if (! isDesktopSidebar()) {
            document.body.classList.remove('sidebar-collapsed');
            return;
        }

        closeMobileSidebar();

        const collapsed = localStorage.getItem(sidebarStorageKey) === '1';
        document.body.classList.toggle('sidebar-collapsed', collapsed);
    };

    if (toggleBtn && sidebar) {
        applyDesktopSidebarState();

        toggleBtn.addEventListener('click', () => {
            if (isDesktopSidebar()) {
                const collapsed = document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
                return;
            }

            sidebar.classList.contains('show') ? closeMobileSidebar() : openMobileSidebar();
        });

        if (overlay) {
            overlay.addEventListener('click', closeMobileSidebar);
        }

        desktopSidebarQuery.addEventListener('change', applyDesktopSidebarState);
    }

    document.querySelectorAll('[data-sidebar-collapse]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (isDesktopSidebar() && document.body.classList.contains('sidebar-collapsed')) {
                document.body.classList.remove('sidebar-collapsed');
                localStorage.setItem(sidebarStorageKey, '0');
            }

            const target = document.querySelector(trigger.dataset.sidebarCollapse);
            if (target) {
                const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                trigger.setAttribute('aria-expanded', !isExpanded);
                target.classList.toggle('d-none', isExpanded);
            }
        });
    });

    document.querySelectorAll('[data-datatable]').forEach((table) => {
        stripColspanPlaceholderRows(table);

        const options = table.dataset.datatableOptions
            ? JSON.parse(table.dataset.datatableOptions)
            : {};

        const dataTable = new DataTable(table, {
            autoWidth: false,
            pageLength: 10,
            language: {
                search: i18n.datatable.search,
                searchPlaceholder: i18n.datatable.searchPlaceholder,
                lengthMenu: i18n.datatable.lengthMenu,
                info: i18n.datatable.info,
                emptyTable: i18n.datatable.emptyTable,
                zeroRecords: i18n.datatable.zeroRecords,
                paginate: {
                    previous: `<i class="ti ${chevronPrev}"></i>`,
                    next: `<i class="ti ${chevronNext}"></i>`,
                },
            },
            ...options,
        });

        initTableDropdowns(table);

        dataTable.on('draw', () => {
            resetTableDropdowns(table);
            initTableDropdowns(table);
        });
    });

    document.querySelectorAll('[data-chart]').forEach((el) => {
        const raw = el.dataset.chart;

        if (! raw) {
            return;
        }

        try {
            const config = JSON.parse(raw);

            if (! config?.chart) {
                return;
            }

            el.innerHTML = '';
            const chart = new ApexCharts(el, config);
            chart.render();
        } catch (error) {
            console.error('Dashboard chart failed to render', error);
        }
    });

    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');

    if (flashSuccess) {
        Swal.fire({
            icon: 'success',
            title: i18n.success,
            text: flashSuccess.dataset.flashSuccess,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    }

    if (flashError) {
        Swal.fire({
            icon: 'error',
            title: i18n.error,
            text: flashError.dataset.flashError,
            confirmButtonColor: '#0F766E',
            cancelButtonText: i18n.cancel,
        });
    }

    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            const message = el.dataset.confirm || i18n.confirmMessage;
            const form = el.closest('form');

            Swal.fire({
                title: i18n.confirmTitle,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0F766E',
                cancelButtonColor: '#64748B',
                confirmButtonText: i18n.confirmYes,
                cancelButtonText: i18n.cancel,
            }).then((result) => {
                if (result.isConfirmed) {
                    if (form) {
                        form.submit();
                    } else if (el.href) {
                        window.location.href = el.href;
                    }
                }
            });
        });
    });

    initLocationPickers();
    initTransportationLocationPickers();
    initSpecialtyPickers();
    initActivityParticipantMultiselects();
    initTableDropdowns();
    initPatientNavbarSearch();
});

export { bootstrap, DataTable, ApexCharts, Swal };
