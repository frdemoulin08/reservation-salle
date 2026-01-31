const setButtonState = (button, state) => {
    const labels = {
        idle: 'Copier le mot de passe',
        copied: 'Copié',
        empty: 'Aucun mot de passe à copier',
    };
    const label = button.dataset.copyText || labels.idle;
    const copiedLabel = button.dataset.copyTextCopied || labels.copied;
    const emptyLabel = button.dataset.copyTextEmpty || labels.empty;
    const text = button.querySelector('.copy-text');
    const copyIcon = button.querySelector('.copy-icon');
    const copiedIcon = button.querySelector('.copied-icon');

    if (state === 'copied') {
        button.dataset.copyState = 'copied';
        button.setAttribute('aria-label', copiedLabel);
        if (text) {
            text.textContent = copiedLabel;
        }
        copyIcon?.classList.add('hidden');
        copiedIcon?.classList.remove('hidden');
        return;
    }

    if (state === 'empty') {
        button.dataset.copyState = 'copy';
        button.setAttribute('aria-label', emptyLabel);
        if (text) {
            text.textContent = label;
        }
        copyIcon?.classList.remove('hidden');
        copiedIcon?.classList.add('hidden');
        return;
    }

    button.dataset.copyState = 'copy';
    button.setAttribute('aria-label', label);
    if (text) {
        text.textContent = label;
    }
    copyIcon?.classList.remove('hidden');
    copiedIcon?.classList.add('hidden');
};

const updateDisabledState = (button, input) => {
    const isEmpty = !input.value;
    button.disabled = isEmpty;
    if (isEmpty) {
        button.classList.add('opacity-50', 'cursor-not-allowed');
        setButtonState(button, 'empty');
    } else {
        button.classList.remove('opacity-50', 'cursor-not-allowed');
        setButtonState(button, 'idle');
    }
};

export const initPasswordCopy = () => {
    const buttons = document.querySelectorAll('[data-password-copy]');

    buttons.forEach((button) => {
        const targetId = button.dataset.passwordTarget;
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        updateDisabledState(button, input);

        input.addEventListener('input', () => updateDisabledState(button, input));

        button.addEventListener('click', async () => {
            if (!input.value) {
                updateDisabledState(button, input);
                return;
            }

            try {
                await navigator.clipboard.writeText(input.value);
                setButtonState(button, 'copied');
                window.setTimeout(() => setButtonState(button, 'idle'), 1500);
            } catch {
                window.alert("Impossible de copier le mot de passe. Veuillez le copier manuellement.");
            }
        });
    });
};
