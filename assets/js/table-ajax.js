const resolveContainer = (element) => {
    if (!element) {
        return null;
    }

    const targetKey = element.dataset.tableTarget;
    if (targetKey) {
        return document.querySelector(`[data-table-ajax="${targetKey}"]`);
    }

    return element.closest('[data-table-ajax]') ?? document.querySelector('[data-table-ajax]');
};

const syncFormFromUrl = (url, container) => {
    const key = container?.dataset?.tableAjax;
    const form = key
        ? document.querySelector(`[data-table-form][data-table-target="${key}"]`)
        : document.querySelector('[data-table-form]');

    if (!form) {
        return;
    }

    const params = new URL(url, window.location.origin).searchParams;
    const fields = form.querySelectorAll('input[name], select[name]');

    fields.forEach((field) => {
        const value = params.get(field.name);
        if (value === null) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
            } else {
                field.value = '';
            }
            return;
        }

        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = field.value === value;
        } else {
            field.value = value;
        }
    });
};

const fetchFragment = async (url, container) => {
    const response = await fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('Erreur réseau');
    }

    const html = await response.text();
    container.innerHTML = html;

    if (window.history && window.history.pushState) {
        window.history.pushState({}, '', url);
    }

    syncFormFromUrl(url, container);
};

const buildUrlFromForm = (form) => {
    const url = new URL(form.action, window.location.origin);
    const formData = new FormData(form);

    for (const [key, value] of formData.entries()) {
        if (value === '') {
            url.searchParams.delete(key);
            continue;
        }
        url.searchParams.set(key, value.toString());
    }

    return url;
};

export const initTableAjax = () => {
    document.addEventListener('click', (event) => {
        const row = event.target.closest('[data-row-link]');
        if (!row) {
            return;
        }

        if (event.target.closest('a, button, input, select, textarea, label')) {
            return;
        }

        const href = row.dataset.rowLink;
        if (href) {
            window.location.href = href;
        }
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-table-link]');
        if (!link) {
            return;
        }

        const container = resolveContainer(link);
        if (!container) {
            return;
        }

        event.preventDefault();

        fetchFragment(link.href, container).catch(() => {
            window.location.href = link.href;
        });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-table-form]');
        if (!form) {
            return;
        }

        const container = resolveContainer(form);
        if (!container) {
            return;
        }

        event.preventDefault();

        const url = buildUrlFromForm(form);

        fetchFragment(url.toString(), container).catch(() => {
            window.location.href = url.toString();
        });
    });

    const debounceMap = new WeakMap();

    document.addEventListener('input', (event) => {
        const field = event.target.closest('[data-table-search]');
        if (!field) {
            return;
        }

        const form = field.closest('[data-table-form]');
        if (!form) {
            return;
        }

        const container = resolveContainer(form);
        if (!container) {
            return;
        }

        const minLength = Number(field.dataset.minLength ?? 3);
        const value = field.value.trim();

        const existingTimer = debounceMap.get(field);
        if (existingTimer) {
            clearTimeout(existingTimer);
        }

        const timer = setTimeout(() => {
            if (value.length === 0) {
                const url = buildUrlFromForm(form);
                fetchFragment(url.toString(), container).catch(() => {
                    window.location.href = url.toString();
                });
                return;
            }

            if (value.length < minLength) {
                return;
            }

            const url = buildUrlFromForm(form);
            fetchFragment(url.toString(), container).catch(() => {
                window.location.href = url.toString();
            });
        }, 250);

        debounceMap.set(field, timer);
    });
};
