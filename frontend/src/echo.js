import Echo from 'laravel-echo';
 
import Pusher from 'pusher-js';
window.Pusher = Pusher;

export function initEcho(){
    if(window.Echo){
        window.Echo.disconnect()
    }

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],

        auth: {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`
            }
        },
        authEndpoint: import.meta.env.VITE_API_BASE_URL + '/api/broadcasting/auth',
    });

}
