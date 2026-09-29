import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    authEndpoint: document.querySelector('meta[name="broadcast-auth-url"]')?.content ?? '/broadcasting/auth',
    enabledTransports: ['ws', 'wss'],
});

const notificationBadge = document.getElementById('notificationBadge');
const notificationDropdownItems = document.getElementById('notificationDropdownItems');
const currentUserId = document.querySelector('meta[name="user-id"]')?.content;

if (currentUserId && notificationBadge && notificationDropdownItems) {
    window.Echo.private(`App.Models.User.${currentUserId}`)
        .notification((notification) => {
            const currentCount = Number(notificationBadge.dataset.count || notificationBadge.textContent.replace('+', '') || 0);
            const nextCount = currentCount + 1;

            notificationBadge.dataset.count = String(nextCount);
            notificationBadge.textContent = nextCount > 99 ? '99+' : String(nextCount);
            notificationBadge.classList.remove('d-none');

            const item = document.createElement('a');
            const notificationUrlTemplate = document.querySelector('meta[name="notification-url-template"]')?.content;
            item.href = notificationUrlTemplate
                ? notificationUrlTemplate.replace('__NOTIFICATION_ID__', encodeURIComponent(notification.id))
                : `/notifications/${encodeURIComponent(notification.id)}`;
            item.className = 'dropdown-item px-3 py-2 bg-light';
            item.innerHTML = '<div class="d-flex gap-2 align-items-start"><i class="bi bi-envelope-fill text-primary mt-1"></i><div class="small text-wrap"><strong class="d-block text-secondary"></strong><span class="text-muted"></span><small class="d-block text-muted mt-1">Baru saja</small></div></div>';
            item.querySelector('strong').textContent = notification.title || 'Notifikasi baru';
            item.querySelector('span').textContent = notification.message || '';

            const emptyState = notificationDropdownItems.querySelector('.text-center');
            if (emptyState) {
                emptyState.remove();
            }
            notificationDropdownItems.prepend(item);
            while (notificationDropdownItems.children.length > 5) {
                notificationDropdownItems.lastElementChild.remove();
            }
        });
}
