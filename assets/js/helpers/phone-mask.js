const normalizeFrenchNumber = (value) => {
    const digits = value.replace(/\D+/g, '');

    if (digits.startsWith('0033')) {
        return `0${digits.slice(4)}`;
    }

    if (digits.startsWith('33')) {
        return `0${digits.slice(2)}`;
    }

    return digits;
};

const formatFrenchNumber = (value) => {
    const normalized = normalizeFrenchNumber(value).slice(0, 10);
    return normalized.replace(/(\d{2})(?=\d)/g, '$1 ').trim();
};

export const initPhoneMasks = () => {
    const inputs = document.querySelectorAll('[data-phone-mask]');

    inputs.forEach((input) => {
        input.value = formatFrenchNumber(input.value);

        input.addEventListener('input', (event) => {
            const cursor = event.target.selectionStart ?? event.target.value.length;
            const before = event.target.value;
            const formatted = formatFrenchNumber(before);

            event.target.value = formatted;

            const diff = formatted.length - before.length;
            const nextPos = Math.max(0, cursor + diff);
            event.target.setSelectionRange(nextPos, nextPos);
        });
    });
};
