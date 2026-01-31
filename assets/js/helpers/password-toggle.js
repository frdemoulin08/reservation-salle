const setButtonState = (button, isVisible) => {
    const showIcon = button.querySelector('[data-password-toggle-icon="show"]');
    const hideIcon = button.querySelector('[data-password-toggle-icon="hide"]');
    const labelShow = button.dataset.passwordLabelShow || 'Afficher le mot de passe';
    const labelHide = button.dataset.passwordLabelHide || 'Masquer le mot de passe';

    button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
    button.setAttribute('aria-label', isVisible ? labelHide : labelShow);

    if (showIcon) {
        showIcon.classList.toggle('hidden', isVisible);
    }
    if (hideIcon) {
        hideIcon.classList.toggle('hidden', !isVisible);
    }
};

export const initPasswordToggle = () => {
    const toggles = document.querySelectorAll('[data-password-toggle]');

    toggles.forEach((button) => {
        const targetId = button.dataset.passwordTarget;
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        setButtonState(button, input.type === 'text');

        button.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' || event.pointerType === 'touch') {
                event.preventDefault();
            }
        });

        button.addEventListener('click', () => {
            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            setButtonState(button, !isVisible);
            input.focus();
        });
    });
};
