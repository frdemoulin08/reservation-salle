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
        }
        field?.classList.add('is-invalid');
        return false;
    }

    applyClasses(input, validClasses, invalidClasses);
    applyClasses(label, labelValidClasses, labelInvalidClasses);
    input?.setAttribute('aria-invalid', 'false');
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
    field?.classList.remove('is-invalid');
    return true;
};

const validateInput = (field) => {
    const label = field.querySelector('[data-validate-label]');
    const error = field.querySelector('[data-validate-error]');

    if (field.dataset.validateGroup === 'checkboxes') {
        const inputs = Array.from(field.querySelectorAll('input[type="checkbox"]'));
        const checked = inputs.some((input) => input.checked);
        const requiredMessage =
            field.dataset.validateRequiredMessage || 'Sélectionnez au moins un élément';

        if (!checked) {
            return setFieldState({
                field,
                input: null,
                label,
                error,
                message: requiredMessage,
            });
        }

        return setFieldState({
            field,
            input: null,
            label,
            error,
            message: '',
        });
    }

    const input = field.querySelector('[data-validate-input]');
    if (!input) {
        return true;
    }

    const value = input.value ?? '';
    const trimmed = value.trim();
    const required = input.required || input.dataset.validateRequired === 'true';

    if (required && trimmed === '') {
        return setFieldState({
            field,
            input,
            label,
            error,
            message: input.dataset.validateRequiredMessage || 'Ce champ est obligatoire',
        });
    }

    if (input.type === 'email' && trimmed !== '' && input.validity && input.validity.typeMismatch) {
        return setFieldState({
            field,
            input,
            label,
            error,
            message: input.dataset.validateEmailMessage || 'Le format de l’email est invalide',
        });
    }

    const pattern = input.dataset.validatePattern || input.getAttribute('pattern');
    if (pattern && trimmed !== '') {
        const regex = new RegExp(pattern);
        if (!regex.test(trimmed)) {
            return setFieldState({
                field,
                input,
                label,
                error,
                message:
                    input.dataset.validatePatternMessage ||
                    input.dataset.validateEmailMessage ||
                    'Le format est invalide',
            });
        }
    }

    return setFieldState({
        field,
        input,
        label,
        error,
        message: '',
    });
};

export const initFormValidation = () => {
    const forms = document.querySelectorAll('[data-validate-form]');

    forms.forEach((form) => {
        const fields = Array.from(form.querySelectorAll('[data-validate-field]'));

        fields.forEach((field) => {
            if (field.dataset.validateGroup === 'checkboxes') {
                const inputs = Array.from(field.querySelectorAll('input[type="checkbox"]'));
                const label = field.querySelector('[data-validate-label]');
                const error = field.querySelector('[data-validate-error]');

                if (error && error.textContent.trim() !== '') {
                    setFieldState({ field, input: null, label, error, message: error.textContent.trim() });
                }

                inputs.forEach((input) => {
                    input.addEventListener('change', () => {
                        field.dataset.validateTouched = 'true';
                        validateInput(field);
                    });
                });

                return;
            }

            const input = field.querySelector('[data-validate-input]');
            const error = field.querySelector('[data-validate-error]');
            const label = field.querySelector('[data-validate-label]');

            if (!input) {
                return;
            }

            if (error && error.textContent.trim() !== '') {
                setFieldState({ field, input, label, error, message: error.textContent.trim() });
            }

            input.addEventListener('blur', () => {
                if (field.dataset.validateTouched === 'true') {
                    validateInput(field);
                }
            });

            input.addEventListener('input', () => {
                if (field.dataset.validateTouched !== 'true') {
                    field.dataset.validateTouched = 'true';
                }
                validateInput(field);
            });
        });

        form.addEventListener('submit', (event) => {
            let isValid = true;
            fields.forEach((field) => {
                field.dataset.validateTouched = 'true';
                const fieldValid = validateInput(field);
                if (!fieldValid) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault();
            }
        });
    });
};
