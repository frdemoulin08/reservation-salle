const portalizeDropdown = (menu) => {
    if (!menu || menu.dataset.dropdownPortalized === 'true') {
        return;
    }

    menu.dataset.dropdownPortalized = 'true';
    document.body.appendChild(menu);
};

export const initDropdownPortals = () => {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-dropdown-toggle]');
        if (!trigger) {
            return;
        }

        const dropdownId = trigger.getAttribute('data-dropdown-toggle');
        if (!dropdownId) {
            return;
        }

        const menu = document.getElementById(dropdownId);
        if (!menu || !menu.hasAttribute('data-dropdown-portal')) {
            return;
        }

        portalizeDropdown(menu);
    }, true);
};
