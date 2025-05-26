/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const reverbPort = import.meta.env.VITE_REVERB_PORT;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

// --- DEBUGGING LOGS ---
console.log('VITE_REVERB_APP_KEY:', reverbAppKey);
console.log('VITE_REVERB_HOST:', reverbHost);
console.log('VITE_REVERB_PORT:', reverbPort);
console.log('VITE_REVERB_SCHEME:', reverbScheme);
// --- END DEBUGGING LOGS ---

if (reverbAppKey && reverbHost && reverbPort) {
  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbAppKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
  });
  console.log('Laravel Echo initialized with Reverb configuration:', {
    key: reverbAppKey, // This is the key Echo will use
    wsHost: reverbHost,
    wsPort: reverbPort,
    forceTLS: reverbScheme === 'https',
  });
} else {
  console.error('Reverb environment variables are not set correctly. Echo will not be initialized.');
  console.error({ reverbAppKey, reverbHost, reverbPort, reverbScheme });
}
