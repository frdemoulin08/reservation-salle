const syncNotificationBadge = (badge) => {
    if (!badge) {
        return;
    }
    const current = Number(badge.textContent || 0);
    badge.classList.toggle('hidden', !Number.isFinite(current) || current <= 0);
};

const initNotificationBadge = ({
    badgeId = 'notification-badge',
    markAllReadId = 'mark-all-read',
    eventName = 'reservation:new-notification',
} = {}) => {
    const badge = document.getElementById(badgeId);
    const markAllReadButton = document.getElementById(markAllReadId);

    if (!badge) {
        return;
    }

    markAllReadButton?.addEventListener('click', () => {
        badge.textContent = '0';
        syncNotificationBadge(badge);
    });

    window.addEventListener(eventName, (event) => {
        const detailCount = Number(event.detail?.count || 1);
        const current = Number(badge.textContent || 0);
        const next = current + (Number.isFinite(detailCount) ? detailCount : 1);
        badge.textContent = String(next);
        syncNotificationBadge(badge);
    });

    syncNotificationBadge(badge);
};

export { initNotificationBadge };
