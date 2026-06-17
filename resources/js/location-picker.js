import {
    $,
    baseSelect2Options,
    destroySelect2,
    resolveLabel,
    setPreselectedOption,
} from './select2-helpers';

function initLocationPicker(wrapper) {
    const countrySelect = wrapper.querySelector('[data-location-country]');
    const citySelect = wrapper.querySelector('[data-location-city]');
    const addCityBtn = wrapper.querySelector('[data-location-add-city]');
    const cityModalEl = wrapper.querySelector('[data-location-city-modal]');
    const newCityName = wrapper.querySelector('[data-location-new-city-name]');
    const newCityNameAr = wrapper.querySelector('[data-location-new-city-name-ar]');
    const cityError = wrapper.querySelector('[data-location-city-error]');
    const saveCityBtn = wrapper.querySelector('[data-location-save-city]');
    const canAddCity = wrapper.dataset.canAddCity === '1';
    const required = wrapper.dataset.required === '1';
    const selectedCountry = wrapper.dataset.selectedCountry || '';
    const selectedCity = wrapper.dataset.selectedCity || '';
    const selectedCountryName = wrapper.dataset.selectedCountryName || '';
    const selectedCityName = wrapper.dataset.selectedCityName || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const cityModal = cityModalEl ? new window.bootstrap.Modal(cityModalEl) : null;

    let activeCountryId = selectedCountry;

    const initCitySelect = async (countryId, cityId = '', cityName = '') => {
        destroySelect2(citySelect);
        citySelect.innerHTML = `<option value="">${wrapper.dataset.placeholderCity}</option>`;

        if (!countryId) {
            citySelect.disabled = true;
            if (addCityBtn) {
                addCityBtn.disabled = true;
            }
            return;
        }

        citySelect.disabled = false;

        const resolvedCityName = cityName || (cityId ? await resolveLabel(
            `${wrapper.dataset.urlCitiesBase}/${countryId}/cities`,
            cityId
        ) : '');

        if (cityId && resolvedCityName) {
            setPreselectedOption(citySelect, cityId, resolvedCityName);
        }

        $(citySelect).select2({
            ...baseSelect2Options(wrapper, wrapper.dataset.placeholderCity, !required),
            minimumInputLength: 0,
            ajax: {
                url: `${wrapper.dataset.urlCitiesBase}/${countryId}/cities`,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term || '',
                    limit: 50,
                }),
                processResults: (data) => ({
                    results: (data.data || []).map((city) => ({
                        id: city.id,
                        text: city.name,
                    })),
                }),
            },
        });

        if (cityId) {
            $(citySelect).val(cityId).trigger('change');
        }

        if (addCityBtn) {
            addCityBtn.disabled = !canAddCity;
        }
    };

    const initCountrySelect = async () => {
        destroySelect2(countrySelect);
        countrySelect.innerHTML = `<option value="">${wrapper.dataset.placeholderCountry}</option>`;

        const resolvedCountryName = selectedCountryName || (selectedCountry
            ? await resolveLabel(wrapper.dataset.urlCountries, selectedCountry)
            : '');

        if (selectedCountry && resolvedCountryName) {
            setPreselectedOption(countrySelect, selectedCountry, resolvedCountryName);
        }

        $(countrySelect).select2({
            ...baseSelect2Options(wrapper, wrapper.dataset.placeholderCountry, !required),
            minimumInputLength: 0,
            ajax: {
                url: wrapper.dataset.urlCountries,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term || '',
                    limit: 50,
                }),
                processResults: (data) => ({
                    results: (data.data || []).map((country) => ({
                        id: country.id,
                        text: country.name,
                    })),
                }),
            },
        });

        if (selectedCountry) {
            $(countrySelect).val(selectedCountry).trigger('change.select2');
            await initCitySelect(selectedCountry, selectedCity, selectedCityName);
        } else {
            await initCitySelect('');
        }

        $(countrySelect).on('change', async function onCountryChange() {
            activeCountryId = $(this).val() || '';
            await initCitySelect(activeCountryId);
        });
    };

    addCityBtn?.addEventListener('click', () => {
        if (!activeCountryId) {
            return;
        }

        if (cityError) {
            cityError.classList.add('d-none');
            cityError.textContent = '';
        }
        if (newCityName) {
            newCityName.value = '';
        }
        if (newCityNameAr) {
            newCityNameAr.value = '';
        }
        cityModal?.show();
    });

    saveCityBtn?.addEventListener('click', async () => {
        if (!activeCountryId || !newCityName?.value.trim()) {
            return;
        }

        const response = await fetch(`${wrapper.dataset.urlCitiesBase}/${activeCountryId}/cities`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({
                name: newCityName.value.trim(),
                name_ar: newCityNameAr?.value.trim() || null,
            }),
        });

        const json = await response.json();

        if (!response.ok) {
            if (cityError) {
                const firstError = json.errors ? Object.values(json.errors)[0]?.[0] : null;
                cityError.textContent = firstError || json.message || 'Error';
                cityError.classList.remove('d-none');
            }
            return;
        }

        await initCitySelect(activeCountryId, String(json.data.id), json.data.name);
        cityModal?.hide();
    });

    initCountrySelect();
}

export function initLocationPickers() {
    document.querySelectorAll('[data-location-picker]').forEach(initLocationPicker);
}
