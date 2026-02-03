const updateDeleteButtonLabel = (card, label) => {
    const photoCard = card.closest('[data-photo-id]');
    const deleteButton = photoCard ? photoCard.querySelector('[data-delete-name]') : null;
    if (deleteButton) {
        deleteButton.dataset.deleteName = label;
    }
};

const updateImageAlt = (card, label) => {
    const photoCard = card.closest('[data-photo-id]');
    const image = photoCard ? photoCard.querySelector('img') : null;
    if (image) {
        image.alt = label;
    }
};

const updatePreviewModalLabel = (photoId, label) => {
    const modalLabel = document.querySelector(`[data-photo-label-modal="${photoId}"]`);
    if (modalLabel) {
        modalLabel.textContent = label;
    }
};

const toggleForm = (elements, isEditing) => {
    elements.form.classList.toggle('hidden', !isEditing);
    elements.editButton.classList.toggle('hidden', isEditing);
    if (isEditing) {
        elements.input.focus();
        elements.input.select();
    }
};

const showError = (elements, message) => {
    elements.error.textContent = message;
    elements.error.classList.remove('hidden');
};

const clearError = (elements) => {
    elements.error.textContent = '';
    elements.error.classList.add('hidden');
};

const saveLabel = async (card, elements) => {
    const updateUrl = card.dataset.updateUrl;
    const csrfToken = card.dataset.csrfToken;
    const newLabel = elements.input.value.trim();

    clearError(elements);

    if (!newLabel) {
        showError(elements, 'Le libellé est obligatoire.');
        return;
    }

    if (!updateUrl || !csrfToken) {
        showError(elements, 'Configuration invalide.');
        return;
    }

    const formData = new FormData();
    formData.append('label', newLabel);
    formData.append('_token', csrfToken);

    try {
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(payload.message || 'Une erreur est survenue.');
        }

        const label = payload.label || newLabel;
        elements.text.textContent = label;
        elements.input.value = label;

        const photoId = card.closest('[data-photo-id]')?.dataset.photoId;
        if (photoId) {
            updatePreviewModalLabel(photoId, label);
        }

        updateDeleteButtonLabel(card, label);
        updateImageAlt(card, label);
        toggleForm(elements, false);
    } catch (error) {
        showError(elements, error.message || 'Une erreur est survenue.');
    }
};

export const initPhotoLabelEditor = () => {
    document.querySelectorAll('[data-photo-label-card]').forEach((card) => {
        if (card.dataset.photoLabelBound === 'true') {
            return;
        }

        const elements = {
            text: card.querySelector('[data-photo-label-text]'),
            form: card.querySelector('[data-photo-label-form]'),
            input: card.querySelector('[data-photo-label-input]'),
            editButton: card.querySelector('[data-photo-label-edit]'),
            saveButton: card.querySelector('[data-photo-label-save]'),
            cancelButton: card.querySelector('[data-photo-label-cancel]'),
            error: card.querySelector('[data-photo-label-error]'),
        };

        if (!elements.text || !elements.form || !elements.input || !elements.editButton || !elements.saveButton || !elements.cancelButton || !elements.error) {
            return;
        }

        card.dataset.photoLabelBound = 'true';

        elements.editButton.addEventListener('click', () => {
            elements.input.value = elements.text.textContent || '';
            clearError(elements);
            toggleForm(elements, true);
        });

        elements.cancelButton.addEventListener('click', () => {
            clearError(elements);
            toggleForm(elements, false);
        });

        elements.saveButton.addEventListener('click', () => {
            saveLabel(card, elements);
        });

        elements.input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                saveLabel(card, elements);
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                clearError(elements);
                toggleForm(elements, false);
            }
        });
    });
};
