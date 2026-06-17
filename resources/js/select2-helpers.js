import $ from 'jquery';
import select2 from 'select2';

select2($);

export function getIsRtl() {
    return document.documentElement.getAttribute('dir') === 'rtl';
}

export function select2Language(wrapper) {
    return {
        noResults: () => wrapper.dataset.i18nNoResults || 'No results found',
        searching: () => wrapper.dataset.i18nSearching || 'Searching...',
        inputTooShort: () => wrapper.dataset.i18nInputTooShort || 'Type to search',
    };
}

export function baseSelect2Options(wrapper, placeholder, allowClear) {
    return {
        theme: 'bootstrap-5',
        width: '100%',
        placeholder,
        allowClear,
        dir: getIsRtl() ? 'rtl' : 'ltr',
        language: select2Language(wrapper),
        dropdownParent: wrapper.closest('.modal') || document.body,
    };
}

export function destroySelect2(select) {
    const $select = $(select);
    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }
}

export function setPreselectedOption(select, id, text) {
    if (!id || !text) {
        return;
    }

    const $select = $(select);
    $select.append(new Option(text, id, true, true));
}

export async function resolveLabel(url, id) {
    if (!id) {
        return null;
    }

    const response = await fetch(`${url}?limit=300`);
    if (!response.ok) {
        return null;
    }

    const json = await response.json();
    const item = (json.data || []).find((row) => String(row.id) === String(id));

    return item?.name ?? null;
}

export { $ };
