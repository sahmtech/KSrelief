import {
    $,
    baseSelect2Options,
    destroySelect2,
} from './select2-helpers';

function initParticipantMultiselect(select) {
    const modal = select.closest('.modal');
    const wrapper = select.closest('[data-participant-multiselect]');

    if (! wrapper) {
        return;
    }

    destroySelect2(select);
    select.disabled = false;

    $(select).select2({
        ...baseSelect2Options(wrapper, wrapper.dataset.placeholder || '', true),
        multiple: true,
        closeOnSelect: false,
        dropdownParent: modal ? $(modal) : $(document.body),
    });
}

function resetParticipantMultiselect(select) {
    destroySelect2(select);
    select.disabled = false;
    select.querySelectorAll('option').forEach((option) => {
        option.selected = false;
    });
    select.closest('form')?.querySelectorAll('[data-participant-hidden]').forEach((input) => input.remove());
}

function selectedValues(select) {
    const values = $(select).val();

    if (! values) {
        return [];
    }

    return (Array.isArray(values) ? values : [values]).filter((value) => value !== '' && value != null);
}

function formHasSelection(form) {
    return Array.from(form.querySelectorAll('[data-participant-select]')).some((select) => selectedValues(select).length > 0);
}

function syncParticipantForm(form) {
    form.querySelectorAll('[data-participant-hidden]').forEach((input) => input.remove());

    form.querySelectorAll('[data-participant-select]').forEach((select) => {
        const name = select.getAttribute('name');
        const values = selectedValues(select);

        Array.from(select.options).forEach((option) => {
            option.selected = values.includes(option.value) || values.includes(String(option.value));
        });

        values.forEach((value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            input.dataset.participantHidden = '1';
            form.appendChild(input);
        });

        select.disabled = true;
    });
}

function showEmptySelectionWarning(form) {
    const message = form.dataset.emptyMessage || 'Select at least one participant.';

    window.Swal?.fire({
        icon: 'warning',
        title: document.body.dataset.i18nError || 'Error',
        text: message,
        confirmButtonColor: '#0F766E',
    });
}

function submitParticipantForm(form) {
    if (! formHasSelection(form)) {
        showEmptySelectionWarning(form);
        return;
    }

    syncParticipantForm(form);
    form.submit();
}

function bindParticipantForm(form) {
    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (! formHasSelection(form)) {
            showEmptySelectionWarning(form);
            return;
        }

        syncParticipantForm(form);
        form.submit();
    });
}

export function initActivityParticipantMultiselects() {
    document.querySelectorAll('[data-participant-multiselect-modal]').forEach((modal) => {
        modal.addEventListener('shown.bs.modal', () => {
            modal.querySelectorAll('[data-participant-select]').forEach(initParticipantMultiselect);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.querySelectorAll('[data-participant-select]').forEach(resetParticipantMultiselect);
        });
    });

    document.querySelectorAll('[data-participant-form]').forEach(bindParticipantForm);

    document.querySelectorAll('button[type="submit"][form]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const form = document.getElementById(button.getAttribute('form') || '');

            if (! form?.matches('[data-participant-form]')) {
                return;
            }

            event.preventDefault();
            submitParticipantForm(form);
        });
    });
}
