const ruleChecks = {
    min: (value, count) => value.length >= count,
    minmax: (value, min, max) => value.length >= min && value.length <= max,
    lowercase: (value) => /[a-z]/.test(value),
    uppercase: (value) => /[A-Z]/.test(value),
    number: (value) => /\d/.test(value),
    special: (value) => /[^A-Za-z0-9]/.test(value),
    allowed: (value, pattern) => {
        if (!value) {
            return false;
        }
        const regex = new RegExp(pattern, 'u');
        return regex.test(value);
    },
    categories: (value, count) => {
        if (!value) {
            return false;
        }
        const checks = [
            /[a-z]/.test(value),
            /[A-Z]/.test(value),
            /\d/.test(value),
            /[^A-Za-z0-9]/.test(value),
        ];
        const met = checks.filter(Boolean).length;
        return met >= count;
    },
};

const updateItemState = (item, state) => {
    item.dataset.met = state === 'met' ? 'true' : 'false';

    const icon = item.querySelector('[data-requirement-icon]');
    if (!icon) {
        return;
    }

    icon.classList.remove('text-fg-success', 'text-fg-danger', 'text-body');

    if (state === 'met') {
        icon.classList.add('text-fg-success');
    } else if (state === 'unmet') {
        icon.classList.add('text-fg-danger');
    } else {
        icon.classList.add('text-body');
    }
};

const evaluateRequirements = (container, value) => {
    const items = container.querySelectorAll('[data-requirement]');

    if (!value) {
        items.forEach((item) => updateItemState(item, 'empty'));
        return;
    }

    items.forEach((item) => {
        const type = item.dataset.requirement ?? '';
        const param = Number(item.dataset.requirementParam ?? 0);
        const min = Number(item.dataset.requirementMin ?? 0);
        const max = Number(item.dataset.requirementMax ?? 0);
        const pattern = item.dataset.requirementPattern ?? '';
        const checker = ruleChecks[type];

        if (!checker) {
            return;
        }

        let isMet = false;

        if (type === 'min') {
            isMet = checker(value, param);
        } else if (type === 'minmax') {
            isMet = checker(value, min, max);
        } else if (type === 'allowed') {
            isMet = checker(value, pattern);
        } else if (type === 'categories') {
            isMet = checker(value, param);
        } else {
            isMet = checker(value);
        }

        updateItemState(item, isMet ? 'met' : 'unmet');
    });
};

export const initPasswordRequirements = () => {
    const containers = document.querySelectorAll('[data-password-requirements]');

    containers.forEach((container) => {
        const inputId = container.dataset.passwordInput;
        const input = inputId ? document.getElementById(inputId) : null;

        if (!input) {
            return;
        }

        evaluateRequirements(container, input.value);

        input.addEventListener('input', (event) => {
            evaluateRequirements(container, event.target.value ?? '');
        });
    });
};
