const AUTOCOMPLETE_SELECTOR = '[data-address-autocomplete="true"]';

const buildSuggestionLine = (item) => {
    const parts = [];
    if (item.label) {
        parts.push(item.label);
    }
    if (item.context) {
        parts.push(item.context);
    }
    return parts.join(' · ');
};

const applySuggestion = (input, suggestion) => {
    const postalId = input.dataset.addressPostalId;
    const cityId = input.dataset.addressCityId;
    const countryId = input.dataset.addressCountryId;
    const sourceId = input.dataset.addressSourceId;
    const externalId = input.dataset.addressExternalId;
    const latitudeId = input.dataset.addressLatitudeId;
    const longitudeId = input.dataset.addressLongitudeId;

    input.value = suggestion.line1 || suggestion.label || '';

    if (postalId) {
        const postalInput = document.getElementById(postalId);
        if (postalInput && suggestion.postcode) {
            postalInput.value = suggestion.postcode;
        }
    }

    if (cityId) {
        const cityInput = document.getElementById(cityId);
        if (cityInput && suggestion.city) {
            cityInput.value = suggestion.city;
        }
    }

    if (countryId) {
        const countryInput = document.getElementById(countryId);
        if (countryInput && !countryInput.value) {
            countryInput.value = 'FR';
        }
    }

    if (sourceId) {
        const sourceInput = document.getElementById(sourceId);
        if (sourceInput) {
            sourceInput.value = 'BAN';
        }
    }

    if (externalId) {
        const externalInput = document.getElementById(externalId);
        if (externalInput && suggestion.id) {
            externalInput.value = suggestion.id;
        }
    }

    if (latitudeId) {
        const latitudeInput = document.getElementById(latitudeId);
        if (latitudeInput && suggestion.latitude) {
            latitudeInput.value = suggestion.latitude;
        }
    }

    if (longitudeId) {
        const longitudeInput = document.getElementById(longitudeId);
        if (longitudeInput && suggestion.longitude) {
            longitudeInput.value = suggestion.longitude;
        }
    }
};

const createDropdown = (wrapper) => {
    const dropdown = document.createElement('div');
    dropdown.className = 'absolute z-20 mt-2 w-full rounded-base border border-default bg-neutral-primary shadow-md hidden';
    dropdown.innerHTML = '<ul class="max-h-64 overflow-auto py-1 text-sm"></ul>';
    wrapper.appendChild(dropdown);
    return dropdown;
};

const renderSuggestions = (dropdown, input, suggestions) => {
    const list = dropdown.querySelector('ul');
    list.innerHTML = '';

    suggestions.forEach((item) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className =
            'flex w-full flex-col gap-1 px-3 py-2 text-left text-body hover:bg-neutral-secondary-medium';
        button.innerHTML = `
            <span class="font-medium text-heading">${item.label || item.line1 || '—'}</span>
            <span class="text-xs text-body">${buildSuggestionLine(item)}</span>
        `;
        button.addEventListener('click', () => {
            applySuggestion(input, item);
            dropdown.classList.add('hidden');
        });
        list.appendChild(button);
    });

    dropdown.classList.toggle('hidden', suggestions.length === 0);
};

const fetchSuggestions = async (endpoint, query) => {
    const url = new URL(endpoint, window.location.origin);
    url.searchParams.set('q', query);
    url.searchParams.set('limit', '6');

    const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        return [];
    }

    const payload = await response.json();
    return Array.isArray(payload.results) ? payload.results : [];
};

const debounce = (callback, delay = 300) => {
    let timeout = null;
    return (...args) => {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => callback(...args), delay);
    };
};

const isFranceSelected = (input) => {
    const countryId = input.dataset.addressCountryId;
    if (!countryId) {
        return true;
    }
    const countryInput = document.getElementById(countryId);
    if (!countryInput) {
        return true;
    }
    return (countryInput.value || 'FR').toUpperCase() === 'FR';
};

const updateManualSource = (input) => {
    const sourceId = input.dataset.addressSourceId;
    const externalId = input.dataset.addressExternalId;
    const latitudeId = input.dataset.addressLatitudeId;
    const longitudeId = input.dataset.addressLongitudeId;

    if (sourceId) {
        const sourceInput = document.getElementById(sourceId);
        if (sourceInput) {
            sourceInput.value = 'MANUAL';
        }
    }

    [externalId, latitudeId, longitudeId].forEach((id) => {
        if (!id) {
            return;
        }
        const field = document.getElementById(id);
        if (field) {
            field.value = '';
        }
    });
};

const updateHint = (wrapper, input) => {
    const hint = wrapper.querySelector('[data-address-hint]');
    if (!hint) {
        return;
    }

    const frText = hint.dataset.addressHintFr || '';
    const foreignText = hint.dataset.addressHintForeign || '';
    const isFrance = isFranceSelected(input);

    hint.textContent = isFrance ? frText : foreignText;
};

export const initAddressAutocomplete = () => {
    document.querySelectorAll(AUTOCOMPLETE_SELECTOR).forEach((input) => {
        const wrapper = input.closest('[data-validate-field]') || input.parentElement;
        if (!wrapper) {
            return;
        }

        const endpoint = input.dataset.addressEndpoint;
        if (!endpoint) {
            return;
        }

        const dropdown = createDropdown(wrapper);
        updateHint(wrapper, input);
        const handleInput = debounce(async () => {
            const query = input.value.trim();
            if (!isFranceSelected(input)) {
                dropdown.classList.add('hidden');
                return;
            }
            if (query.length < 3) {
                dropdown.classList.add('hidden');
                return;
            }

            const suggestions = await fetchSuggestions(endpoint, query);
            renderSuggestions(dropdown, input, suggestions);
        }, 300);

        input.addEventListener('input', handleInput);
        input.addEventListener('blur', () => {
            window.setTimeout(() => dropdown.classList.add('hidden'), 150);
        });

        const countryId = input.dataset.addressCountryId;
        if (countryId) {
            const countryInput = document.getElementById(countryId);
            if (countryInput) {
                countryInput.addEventListener('change', () => {
                    updateHint(wrapper, input);
                    if (!isFranceSelected(input)) {
                        dropdown.classList.add('hidden');
                        updateManualSource(input);
                    }
                });
            }
        }
    });
};
