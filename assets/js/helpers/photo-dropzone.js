import { initPhotoLabelEditor } from './photo-label-editor.js';

const escapeHtml = (value) => {
    if (typeof value !== 'string') {
        return '';
    }

    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

const createPhotoCard = (photo) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'group overflow-hidden rounded-base border border-default bg-neutral-primary shadow-xs';
    wrapper.dataset.photoId = String(photo.id ?? '');

    const label = escapeHtml(photo.label || 'Photo');
    const createdAt = escapeHtml(photo.createdAt || '');
    const updateUrl = escapeHtml(photo.updateUrl || '');
    const updateToken = escapeHtml(photo.updateToken || '');

    wrapper.innerHTML = `
        <div class="relative aspect-[4/3] overflow-hidden bg-neutral-secondary-medium">
            <img src="${escapeHtml(photo.url)}" alt="${label}" class="h-full w-full object-cover" loading="lazy">
            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-dark/40 opacity-0 transition-opacity group-hover:opacity-100">
                <button type="button" class="rounded-base bg-neutral-primary px-3 py-1.5 text-xs font-medium text-heading shadow-xs" data-modal-target="venue-photo-preview-${photo.id}" data-modal-toggle="venue-photo-preview-${photo.id}">
                    Aperçu
                </button>
                <button type="button" class="rounded-base bg-danger px-3 py-1.5 text-xs font-medium text-white shadow-xs" data-modal-target="venue-photo-delete-modal" data-modal-toggle="venue-photo-delete-modal" data-delete-url="${escapeHtml(photo.deleteUrl)}" data-delete-token="${escapeHtml(photo.deleteToken)}" data-delete-name="${label}">
                    Supprimer
                </button>
            </div>
        </div>
        <div class="p-3" data-photo-label-card data-update-url="${updateUrl}" data-csrf-token="${updateToken}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-heading" data-photo-label-text>${label}</p>
                    <p class="text-xs text-body-subtle">${createdAt}</p>
                </div>
                <button type="button" class="text-xs text-body-subtle hover:text-heading" data-photo-label-edit>Renommer</button>
            </div>
            <div class="mt-2 hidden" data-photo-label-form>
                <input type="text" class="w-full rounded-base border border-default bg-neutral-primary px-3 py-2 text-xs text-heading" value="${label}" data-photo-label-input>
                <div class="mt-2 flex items-center gap-2">
                    <button type="button" class="rounded-base bg-brand-strong px-3 py-1.5 text-xs font-medium text-white" data-photo-label-save>Enregistrer</button>
                    <button type="button" class="rounded-base border border-default bg-neutral-primary px-3 py-1.5 text-xs font-medium text-body" data-photo-label-cancel>Annuler</button>
                </div>
                <p class="mt-2 text-xs text-danger hidden" data-photo-label-error></p>
            </div>
        </div>
    `;

    return wrapper;
};

const createPhotoPreviewModal = (photo) => {
    const modal = document.createElement('div');
    modal.id = `venue-photo-preview-${photo.id}`;
    modal.tabIndex = -1;
    modal.className = 'hidden fixed left-0 right-0 top-0 z-50 h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0';

    const label = escapeHtml(photo.label || 'Photo');
    const createdAt = escapeHtml(photo.createdAt || '');

    modal.innerHTML = `
        <div class="relative w-full max-w-4xl p-4">
            <div class="relative rounded-base border border-default bg-neutral-primary-soft p-4 shadow-sm md:p-6">
                <button type="button" class="absolute end-2.5 top-3 inline-flex h-9 w-9 items-center justify-center rounded-base bg-transparent text-sm text-body hover:bg-neutral-tertiary hover:text-heading" data-modal-hide="venue-photo-preview-${photo.id}">
                    <span class="sr-only">Fermer</span>
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                    </svg>
                </button>
                <div class="flex flex-col gap-4">
                    <img src="${escapeHtml(photo.url)}" alt="${label}" class="max-h-[70vh] w-full rounded-base object-contain">
                    <div>
                        <p class="text-sm font-medium text-heading" data-photo-label-modal="${photo.id}">${label}</p>
                        <p class="text-xs text-body-subtle">${createdAt}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

    return modal;
};

const normalizeFiles = (files) => {
    if (!files) {
        return [];
    }

    return Array.from(files).filter((file) => file instanceof File);
};

const setDropzoneState = (container, isActive) => {
    const label = container.querySelector('[data-photo-dropzone-label]');
    if (!label) {
        return;
    }

    if (isActive) {
        label.classList.add('bg-neutral-secondary-soft');
        label.classList.add('border-brand-medium');
    } else {
        label.classList.remove('bg-neutral-secondary-soft');
        label.classList.remove('border-brand-medium');
    }
};

const uploadFiles = async (container, files) => {
    const uploadUrl = container.dataset.uploadUrl;
    const csrfToken = container.dataset.csrfToken;
    const errorTarget = container.querySelector('[data-photo-error]');
    const galleryId = container.dataset.galleryTarget;
    const gallery = galleryId ? document.getElementById(galleryId) : null;
    const addTile = gallery ? gallery.querySelector('[data-photo-add-tile]') : null;
    const emptyState = gallery ? gallery.querySelector('[data-photo-empty]') : null;
    const status = container.querySelector('[data-photo-status]');

    if (!uploadUrl || files.length === 0) {
        return;
    }

    if (errorTarget) {
        errorTarget.textContent = '';
    }

    const formData = new FormData();
    files.forEach((file) => {
        formData.append('photos[]', file);
    });
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    container.setAttribute('aria-busy', 'true');
    if (status) {
        status.textContent = 'Téléversement en cours...';
    }

    try {
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(payload.message || 'Une erreur est survenue lors de l\'upload.');
        }

        if (Array.isArray(payload.photos) && gallery) {
            payload.photos.forEach((photo) => {
                const card = createPhotoCard(photo);
                if (addTile) {
                    gallery.insertBefore(card, addTile);
                } else {
                    gallery.appendChild(card);
                }

                const modal = createPhotoPreviewModal(photo);
                document.body.appendChild(modal);
            });
            if (emptyState) {
                emptyState.remove();
            }
            initPhotoLabelEditor();
        }
    } catch (error) {
        if (errorTarget) {
            errorTarget.textContent = error.message || 'Une erreur est survenue.';
        }
    } finally {
        container.removeAttribute('aria-busy');
        if (status) {
            status.textContent = 'JPG, PNG, WEBP · 5 Mo max';
        }
    }
};

export const initPhotoDropzone = () => {
    document.querySelectorAll('[data-photo-dropzone]').forEach((container) => {
        const input = container.querySelector('[data-photo-input]');
        const submit = container.querySelector('[data-photo-submit]');
        const label = container.querySelector('[data-photo-dropzone-label]');
        const galleryId = container.dataset.galleryTarget;
        const gallery = galleryId ? document.getElementById(galleryId) : null;
        const addTile = gallery ? gallery.querySelector('[data-photo-add-tile]') : null;

        if (submit) {
            submit.classList.add('hidden');
        }

        if (input) {
            input.addEventListener('change', async (event) => {
                const files = normalizeFiles(event.target.files);
                await uploadFiles(container, files);
                event.target.value = '';
            });
        }

        if (label) {
            if (input) {
                label.addEventListener('click', (event) => {
                    if (event.target === input) {
                        return;
                    }
                    event.preventDefault();
                    input.click();
                });

                label.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        input.click();
                    }
                });
            }

            label.addEventListener('dragenter', (event) => {
                event.preventDefault();
                setDropzoneState(container, true);
            });

            label.addEventListener('dragover', (event) => {
                event.preventDefault();
                setDropzoneState(container, true);
            });

            label.addEventListener('dragleave', (event) => {
                event.preventDefault();
                setDropzoneState(container, false);
            });

            label.addEventListener('drop', async (event) => {
                event.preventDefault();
                setDropzoneState(container, false);
                const files = normalizeFiles(event.dataTransfer?.files);
                await uploadFiles(container, files);
            });
        }

        if (addTile && input) {
            addTile.addEventListener('click', (event) => {
                event.preventDefault();
                input.click();
            });
        }
    });
};
