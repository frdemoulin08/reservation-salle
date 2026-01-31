const DEFAULT_ALLOWED_SPECIALS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_{|}~`€£¥§¤';

const buildPool = () => ({
    lower: 'abcdefghijklmnopqrstuvwxyz',
    upper: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    digit: '0123456789',
    special: DEFAULT_ALLOWED_SPECIALS,
});

const pick = (chars) => chars[Math.floor(Math.random() * chars.length)];

const shuffle = (value) => {
    const array = value.split('');
    for (let index = array.length - 1; index > 0; index -= 1) {
        const swap = Math.floor(Math.random() * (index + 1));
        [array[index], array[swap]] = [array[swap], array[index]];
    }
    return array.join('');
};

const generatePassword = (length) => {
    const pool = buildPool();
    const mandatory = [
        pick(pool.lower),
        pick(pool.upper),
        pick(pool.digit),
        pick(pool.special),
    ];
    const all = pool.lower + pool.upper + pool.digit + pool.special;

    let password = mandatory.join('');
    while (password.length < length) {
        password += pick(all);
    }
    return shuffle(password);
};

const toggleModal = (modal, isOpen) => {
    if (!modal) {
        return;
    }

    if (isOpen) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
    } else {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
    }
};

export const initPasswordGenerator = () => {
    const buttons = document.querySelectorAll('[data-password-generate]');

    buttons.forEach((button) => {
        const targetId = button.dataset.passwordTarget;
        const input = targetId ? document.getElementById(targetId) : null;
        const modalId = button.dataset.passwordModalTarget;
        const modal = modalId ? document.getElementById(modalId) : null;

        if (!input) {
            return;
        }

        const length = Number(button.dataset.passwordLength || 16);

        button.addEventListener('click', () => {
            if (input.value && modal) {
                modal.dataset.passwordTarget = targetId || '';
                modal.dataset.passwordLength = String(length);
                toggleModal(modal, true);
                return;
            }

            input.type = 'text';
            input.value = generatePassword(length);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
        });
    });

    document.addEventListener('click', (event) => {
        const confirmButton = event.target.closest('[data-password-generate-confirm]');
        if (!confirmButton) {
            return;
        }

        const modal = confirmButton.closest('[data-password-generate-modal]');
        if (!modal) {
            return;
        }

        const targetId = modal.dataset.passwordTarget;
        const length = Number(modal.dataset.passwordLength || 16);
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            toggleModal(modal, false);
            return;
        }

        input.type = 'text';
        input.value = generatePassword(length);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.focus();
        toggleModal(modal, false);
    });
};
