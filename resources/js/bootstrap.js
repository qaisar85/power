import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Realtime: Initialize Echo client with Pusher when env vars are present
(async () => {
  try {
    const key = import.meta.env.VITE_PUSHER_APP_KEY;
    if (!key) return; // Skip if Echo/Pusher not configured

    const { default: Echo } = await import('laravel-echo');
    const { default: Pusher } = await import('pusher-js');
    window.Pusher = Pusher;

    window.Echo = new Echo({
      broadcaster: 'pusher',
      key,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
      wsHost: import.meta.env.VITE_PUSHER_HOST ?? (window.location.hostname || '127.0.0.1'),
      wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? '6001'),
      wssPort: Number(import.meta.env.VITE_PUSHER_PORT ?? '6001'),
      forceTLS: import.meta.env.VITE_PUSHER_FORCE_TLS === 'true',
      enabledTransports: ['ws', 'wss'],
    });

    console.log('Echo initialized');
  } catch (e) {
    console.warn('Echo/Pusher not initialized; falling back to polling.', e?.message ?? e);
  }
})();
