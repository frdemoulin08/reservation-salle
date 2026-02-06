const LOOKUP_SELECTOR = '[data-company-lookup="true"]';

const normalizeSiret = (value) => (value || '').replace(/\D+/g, '');

const passesLuhn = (value) => {
    let sum = 0;
    const length = value.length;
    for (let i = length - 1; i >= 0; i -= 1) {
        const digit = Number(value[i]);
        if ((i % 2) ^ (length % 2)) {
            sum += digit;
        } else {
            sum += Math.floor((2 * digit) / 10) + (2 * digit) % 10;
        }
    }
    return sum > 0 && sum % 10 === 0;
};

const isValidSiret = (value) => /^\d{14}$/.test(value) && passesLuhn(value);

const splitClasses = (value) => (value ? value.split(' ').filter(Boolean) : []);

const applyClasses = (element, addClasses, removeClasses) => {
    if (!element) {
        return;
    }
    removeClasses.forEach((className) => element.classList.remove(className));
    addClasses.forEach((className) => element.classList.add(className));
};

const setFieldState = ({ field, input, label, error, message }) => {
    const validClasses = splitClasses(input?.dataset.validateValidClass);
    const invalidClasses = splitClasses(input?.dataset.validateInvalidClass);
    const labelValidClasses = splitClasses(label?.dataset.validateValidClass);
    const labelInvalidClasses = splitClasses(label?.dataset.validateInvalidClass);

    if (message) {
        applyClasses(input, invalidClasses, validClasses);
        applyClasses(label, labelInvalidClasses, labelValidClasses);
        input?.setAttribute('aria-invalid', 'true');
        if (error) {
            error.textContent = message;
            error.classList.remove('hidden');
            error.classList.remove('text-success', 'text-body');
            error.classList.add('text-danger');
            error.classList.add('dark:text-fg-danger-strong');
        }
        field?.classList.add('is-invalid');
        return;
    }

    applyClasses(input, validClasses, invalidClasses);
    applyClasses(label, labelValidClasses, labelInvalidClasses);
    input?.setAttribute('aria-invalid', 'false');
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
        error.classList.remove('text-success', 'text-body');
        error.classList.add('text-danger');
        error.classList.add('dark:text-fg-danger-strong');
    }
    field?.classList.remove('is-invalid');
};

const getHintElement = (wrapper) => wrapper.querySelector('[data-company-hint]');

const setHint = (wrapper, message, tone = 'info') => {
    const hint = getHintElement(wrapper);
    if (!hint) {
        return;
    }

    hint.textContent = message || '';
    hint.classList.remove('text-success', 'text-body');
    hint.classList.add(tone === 'success' ? 'text-success' : 'text-body');
};

const resetHint = (wrapper) => {
    const hint = getHintElement(wrapper);
    if (!hint) {
        return;
    }
    setHint(wrapper, hint.dataset.companyHintDefault || '', 'info');
};

const debounce = (callback, delay = 350) => {
    let timeout = null;
    return (...args) => {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => callback(...args), delay);
    };
};

const updateFieldValue = (fieldId, value) => {
    if (!fieldId || value === null || value === undefined || value === '') {
        return;
    }
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }
    field.value = value;
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));
};

const updateSelectValue = (fieldId, value) => {
    if (!fieldId || value === null || value === undefined || value === '') {
        return;
    }
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }
    const option = Array.from(field.options).find((item) => item.value === value && !item.disabled);
    if (!option) {
        return;
    }
    field.value = value;
    field.dispatchEvent(new Event('change', { bubbles: true }));
};

const updateCheckboxValue = (fieldId, value) => {
    if (!fieldId || value === null || value === undefined) {
        return;
    }
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }
    field.checked = Boolean(value);
    field.dispatchEvent(new Event('change', { bubbles: true }));
};

const applyCompanyData = (input, data) => {
    updateFieldValue(input.dataset.companyLegalNameId, data.legalName);
    updateFieldValue(input.dataset.companyDisplayNameId, data.displayName);
    updateFieldValue(input.dataset.companyOrganizationTypeId, data.organizationType);
    if (data.organizationType === 'ASSOCIATION') {
        updateCheckboxValue(input.dataset.companyAssociationRegisteredId, true);
    }
    updateLegalNatureOptions(input);
    updateSelectValue(input.dataset.companyLegalNatureId, data.legalNature);

    const address = data.address || {};
    updateFieldValue(input.dataset.companyAddressLine1Id, address.line1);
    updateFieldValue(input.dataset.companyAddressLine2Id, address.line2);
    updateFieldValue(input.dataset.companyAddressLine3Id, address.line3);
    updateFieldValue(input.dataset.companyAddressPostalId, address.postalCode);
    updateFieldValue(input.dataset.companyAddressCityId, address.city);
    updateFieldValue(input.dataset.companyAddressCountryId, address.country);
};

const getCountryValue = (input) => {
    const countryId = input.dataset.companyCountryId;
    if (!countryId) {
        return 'FR';
    }
    const countryInput = document.getElementById(countryId);
    if (!countryInput) {
        return 'FR';
    }
    if (!countryInput.value) {
        return '';
    }
    return countryInput.value.toUpperCase();
};

const getOrganizationType = (input) => {
    const typeId = input.dataset.companyOrganizationTypeId;
    if (!typeId) {
        return '';
    }
    const field = document.getElementById(typeId);
    return field ? field.value : '';
};

const updateLegalNatureOptions = (input) => {
    const legalNatureId = input.dataset.companyLegalNatureId;
    if (!legalNatureId) {
        return;
    }
    const select = document.getElementById(legalNatureId);
    if (!select) {
        return;
    }
    const type = getOrganizationType(input);
    const options = Array.from(select.options);
    options.forEach((option) => {
        if (!option.value) {
            return;
        }
        const types = (option.dataset.legalTypes || '').split(',').map((item) => item.trim()).filter(Boolean);
        const allowed = !type || types.length === 0 || types.includes(type);
        option.hidden = !allowed;
        option.disabled = !allowed;
    });

    if (select.value) {
        const current = options.find((option) => option.value === select.value);
        if (!current || current.disabled) {
            select.value = '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
};

const isAssociationRegistered = (input) => {
    const fieldId = input.dataset.companyAssociationRegisteredId;
    if (!fieldId) {
        return false;
    }
    const field = document.getElementById(fieldId);
    return Boolean(field && field.checked);
};

const isSiretRequired = (input) => {
    if (getCountryValue(input) !== 'FR') {
        return false;
    }

    const type = getOrganizationType(input);
    if (type === 'ENTREPRISE' || type === 'COLLECTIVITE') {
        return true;
    }

    if (type === 'ASSOCIATION') {
        return isAssociationRegistered(input);
    }

    return false;
};

const updateAssociationField = (input) => {
    const fieldId = input.dataset.companyAssociationRegisteredId;
    if (!fieldId) {
        return;
    }
    const field = document.getElementById(fieldId);
    if (!field) {
        return;
    }
    const wrapper = field.closest('[data-association-registered-field]');
    if (!wrapper) {
        return;
    }

    const isAssociation = getOrganizationType(input) === 'ASSOCIATION';
    wrapper.classList.toggle('hidden', !isAssociation);
    if (!isAssociation && field.checked) {
        field.checked = false;
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }
};

const updateRequiredState = (input, wrapper) => {
    const required = isSiretRequired(input);
    input.required = required;
    if (required) {
        input.dataset.validateRequired = 'true';
    } else {
        delete input.dataset.validateRequired;
    }

    if (!required) {
        setFieldState({
            field: wrapper,
            input,
            label: wrapper.querySelector('[data-validate-label]'),
            error: wrapper.querySelector('[data-validate-error]'),
            message: '',
        });
    }
};

const getInvalidSiretMessage = (input, normalized) => {
    const lengthMessage = input.dataset.validatePatternMessage || '';
    const luhnMessage = input.dataset.companyInvalidLuhn || '';

    if (!/^\d{14}$/.test(normalized)) {
        return lengthMessage || 'Le SIRET doit contenir 14 chiffres.';
    }

    if (!passesLuhn(normalized)) {
        return luhnMessage || 'Le SIRET est invalide.';
    }

    return '';
};

const updateCountryState = (input, wrapper) => {
    const trigger = wrapper.querySelector('[data-company-trigger]');
    const hint = getHintElement(wrapper);
    const countryValue = getCountryValue(input);
    const isFrance = countryValue === 'FR';
    const isUnknown = countryValue === '';

    if (isUnknown) {
        input.disabled = false;
        if (trigger) {
            trigger.disabled = false;
        }
        resetHint(wrapper);
        updateRequiredState(input, wrapper);
        return;
    }

    input.disabled = !isFrance;
    if (trigger) {
        trigger.disabled = !isFrance;
    }

    if (!isFrance) {
        if (hint) {
            setHint(wrapper, hint.dataset.companyHintForeign || '', 'info');
        }
        setFieldState({
            field: wrapper,
            input,
            label: wrapper.querySelector('[data-validate-label]'),
            error: wrapper.querySelector('[data-validate-error]'),
            message: '',
        });
        input.dataset.companyLastLookup = '';
        updateRequiredState(input, wrapper);
        return;
    }

    resetHint(wrapper);
    updateRequiredState(input, wrapper);
};

const performLookup = async (input, wrapper, trigger, origin = 'manual') => {
    const hint = getHintElement(wrapper);
    const normalized = normalizeSiret(input.value);
    const label = wrapper.querySelector('[data-validate-label]');
    const error = wrapper.querySelector('[data-validate-error]');

    if (!normalized) {
        setFieldState({ field: wrapper, input, label, error, message: '' });
        resetHint(wrapper);
        return;
    }

    if (!isValidSiret(normalized)) {
        const message = getInvalidSiretMessage(input, normalized);
        if (message) {
            setFieldState({
                field: wrapper,
                input,
                label,
                error,
                message,
            });
        }
        resetHint(wrapper);
        return;
    }

    if ((origin === 'blur' || origin === 'auto') && input.dataset.companyLastLookup === normalized) {
        return;
    }

    setFieldState({ field: wrapper, input, label, error, message: '' });
    setHint(wrapper, 'Recherche en cours...', 'info');

    const endpointTemplate = input.dataset.companyEndpoint;
    if (!endpointTemplate) {
        return;
    }

    if (trigger) {
        trigger.dataset.companyOriginalLabel = trigger.dataset.companyOriginalLabel || trigger.textContent;
        trigger.textContent = 'Recherche...';
        trigger.disabled = true;
    }

    try {
        const url = endpointTemplate.replace('SIRET_PLACEHOLDER', encodeURIComponent(normalized));
        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (response.status === 400) {
            const payload = await response.json();
            const message = payload.message || getInvalidSiretMessage(input, normalized);
            setFieldState({ field: wrapper, input, label, error, message });
            resetHint(wrapper);
            return;
        }

        if (!response.ok) {
            setFieldState({ field: wrapper, input, label, error, message: '' });
            if (hint) {
                setHint(wrapper, hint.dataset.companyHintUnavailable || '', 'info');
            }
            return;
        }

        const payload = await response.json();
        if (payload.status === 'not_found' || payload.found === false) {
            setFieldState({ field: wrapper, input, label, error, message: '' });
            if (hint) {
                setHint(wrapper, hint.dataset.companyHintNotFound || '', 'info');
            }
            return;
        }

        if (payload.status === 'ok' && payload.data) {
            applyCompanyData(input, payload.data);
            input.dataset.companyLastLookup = normalized;
            setFieldState({ field: wrapper, input, label, error, message: '' });
            if (hint) {
                setHint(wrapper, hint.dataset.companyHintSuccess || '', 'success');
            }
        }
    } catch (error) {
        setFieldState({ field: wrapper, input, label, error, message: '' });
        if (hint) {
            setHint(wrapper, hint.dataset.companyHintUnavailable || '', 'info');
        }
    } finally {
        if (trigger) {
            trigger.textContent = trigger.dataset.companyOriginalLabel || 'Rechercher';
            trigger.disabled = false;
        }
    }
};

export const initCompanyLookup = () => {
    document.querySelectorAll(LOOKUP_SELECTOR).forEach((input) => {
        const wrapper = input.closest('[data-company-field]') || input.parentElement;
        if (!wrapper) {
            return;
        }

        const trigger = wrapper.querySelector('[data-company-trigger]');
        const hint = getHintElement(wrapper);
        if (hint && !hint.textContent.trim()) {
            resetHint(wrapper);
        }

        updateCountryState(input, wrapper);
        updateRequiredState(input, wrapper);
        updateAssociationField(input);
        updateLegalNatureOptions(input);

        const handleInput = debounce(() => {
            if (input.disabled) {
                return;
            }
            const normalized = normalizeSiret(input.value);
            if (!normalized) {
                setFieldState({
                    field: wrapper,
                    input,
                    label: wrapper.querySelector('[data-validate-label]'),
                    error: wrapper.querySelector('[data-validate-error]'),
                    message: '',
                });
                resetHint(wrapper);
                return;
            }

            if (normalized.length < 14) {
                setFieldState({
                    field: wrapper,
                    input,
                    label: wrapper.querySelector('[data-validate-label]'),
                    error: wrapper.querySelector('[data-validate-error]'),
                    message: '',
                });
                resetHint(wrapper);
                return;
            }

            if (normalized.length !== 14) {
                const message = getInvalidSiretMessage(input, normalized);
                setFieldState({
                    field: wrapper,
                    input,
                    label: wrapper.querySelector('[data-validate-label]'),
                    error: wrapper.querySelector('[data-validate-error]'),
                    message,
                });
                return;
            }

            if (!passesLuhn(normalized)) {
                const message = getInvalidSiretMessage(input, normalized);
                setFieldState({
                    field: wrapper,
                    input,
                    label: wrapper.querySelector('[data-validate-label]'),
                    error: wrapper.querySelector('[data-validate-error]'),
                    message,
                });
                return;
            }

            performLookup(input, wrapper, null, 'auto');
        }, 350);

        input.addEventListener('input', handleInput);
        input.addEventListener('blur', () => {
            if (input.disabled) {
                return;
            }
            performLookup(input, wrapper, trigger, 'blur');
        });

        if (trigger) {
            trigger.addEventListener('click', () => {
                if (input.disabled) {
                    return;
                }
                performLookup(input, wrapper, trigger, 'manual');
            });
        }

        const countryId = input.dataset.companyCountryId;
        if (countryId) {
            const countryInput = document.getElementById(countryId);
            if (countryInput) {
                countryInput.addEventListener('change', () => {
                    updateCountryState(input, wrapper);
                });
            }
        }

        const typeId = input.dataset.companyOrganizationTypeId;
        if (typeId) {
            const typeInput = document.getElementById(typeId);
            if (typeInput) {
                typeInput.addEventListener('change', () => {
                    updateAssociationField(input);
                    updateLegalNatureOptions(input);
                    updateRequiredState(input, wrapper);
                });
            }
        }

        const associationId = input.dataset.companyAssociationRegisteredId;
        if (associationId) {
            const associationInput = document.getElementById(associationId);
            if (associationInput) {
                associationInput.addEventListener('change', () => {
                    updateRequiredState(input, wrapper);
                });
            }
        }
    });
};
