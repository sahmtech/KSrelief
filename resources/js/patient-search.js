const MIN_CHARS = 2;
const DEBOUNCE_MS = 280;

function initPatientNavbarSearch() {
    const root = document.querySelector('[data-patient-search]');
    if (!root) {
        return;
    }

    const searchUrl = root.dataset.searchUrl || '/patients/search';

    const input = root.querySelector('[data-patient-search-input]');
    const results = root.querySelector('[data-patient-search-results]');
    const emptyText = root.dataset.emptyText || 'No patients found';
    const loadingText = root.dataset.loadingText || 'Searching...';

    if (!input || !results) {
        return;
    }

    let debounceTimer = null;
    let activeIndex = -1;
    let currentItems = [];

    const hideResults = () => {
        results.hidden = true;
        results.innerHTML = '';
        activeIndex = -1;
        currentItems = [];
        input.setAttribute('aria-expanded', 'false');
    };

    const showResults = () => {
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const navigateTo = (url) => {
        if (url) {
            window.location.href = url;
        }
    };

    const renderResults = (items) => {
        currentItems = items;
        activeIndex = -1;

        if (items.length === 0) {
            results.innerHTML = `<div class="patient-search__empty">${emptyText}</div>`;
            showResults();
            return;
        }

        results.innerHTML = items.map((item, index) => `
            <button
                type="button"
                class="patient-search__item"
                data-index="${index}"
                role="option"
                aria-selected="false"
            >
                <div class="patient-search__item-main">
                    <span class="patient-search__item-name">${escapeHtml(item.name)}</span>
                    ${item.file_number ? `<code class="patient-search__item-code">${escapeHtml(item.file_number)}</code>` : ''}
                </div>
                <div class="patient-search__item-meta">
                    ${item.campaign ? `<span>${escapeHtml(item.campaign)}</span>` : ''}
                    ${item.age ? `<span>${escapeHtml(item.age)}</span>` : ''}
                    ${item.stage ? `<span>${escapeHtml(item.stage)}</span>` : ''}
                </div>
            </button>
        `).join('');

        showResults();

        results.querySelectorAll('.patient-search__item').forEach((button) => {
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            button.addEventListener('click', () => {
                const index = Number(button.dataset.index);
                navigateTo(items[index]?.url);
            });
        });
    };

    const setActiveItem = (index) => {
        const buttons = results.querySelectorAll('.patient-search__item');
        buttons.forEach((button, i) => {
            button.classList.toggle('is-active', i === index);
            button.setAttribute('aria-selected', i === index ? 'true' : 'false');
        });
        activeIndex = index;
    };

    const fetchResults = async (term) => {
        results.innerHTML = `<div class="patient-search__empty">${loadingText}</div>`;
        showResults();

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                hideResults();
                return;
            }

            const data = await response.json();
            renderResults(data.results || []);
        } catch {
            hideResults();
        }
    };

    input.addEventListener('input', () => {
        const term = input.value.trim();
        clearTimeout(debounceTimer);

        if (term.length < MIN_CHARS) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(() => fetchResults(term), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', (event) => {
        if (results.hidden || currentItems.length === 0) {
            if (event.key === 'Enter') {
                const term = input.value.trim();
                if (term.length >= MIN_CHARS) {
                    fetchResults(term).then(() => {
                        if (currentItems.length === 1) {
                            navigateTo(currentItems[0].url);
                        }
                    });
                }
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            const next = activeIndex < currentItems.length - 1 ? activeIndex + 1 : 0;
            setActiveItem(next);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            const prev = activeIndex > 0 ? activeIndex - 1 : currentItems.length - 1;
            setActiveItem(prev);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (activeIndex >= 0) {
                navigateTo(currentItems[activeIndex]?.url);
            } else if (currentItems.length === 1) {
                navigateTo(currentItems[0].url);
            }
        } else if (event.key === 'Escape') {
            hideResults();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            hideResults();
        }
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export { initPatientNavbarSearch };
