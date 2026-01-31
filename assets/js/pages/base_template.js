import { initThemeToggle } from '../helpers/theme.js';
import { initNotificationBadge } from '../helpers/notifications.js';
import { initTableAjax } from '../table-ajax.js';
import { initModalToggles } from '../helpers/modal.js';
import { initPasswordRequirements } from '../helpers/password-requirements.js';
import { initPasswordToggle } from '../helpers/password-toggle.js';
import { initPhoneMasks } from '../helpers/phone-mask.js';
import { initPasswordGenerator } from '../helpers/password-generator.js';
import { initPasswordCopy } from '../helpers/password-copy.js';
import { initDropdownPortals } from '../helpers/dropdown-portal.js';
import { initFormValidation } from '../helpers/form-validation.js';

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNotificationBadge();
    initTableAjax();
    initModalToggles();
    initPasswordRequirements();
    initPasswordToggle();
    initPhoneMasks();
    initPasswordGenerator();
    initPasswordCopy();
    initDropdownPortals();
    initFormValidation();
});
