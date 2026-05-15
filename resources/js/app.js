// Supprime ou commente cette ligne :
// import './bootstrap';

import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Alpine = Alpine;
Alpine.start();

// ── Laravel Echo — Reverb (WebSocket temps réel) ──
window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key:         import.meta.env.VITE_REVERB_APP_KEY      ?? 'btl-reverb-key',
    wsHost:      import.meta.env.VITE_REVERB_HOST          ?? window.location.hostname,
    wsPort:      import.meta.env.VITE_REVERB_PORT          ?? 8080,
    wssPort:     import.meta.env.VITE_REVERB_PORT          ?? 8080,
    forceTLS:   (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    },
});
