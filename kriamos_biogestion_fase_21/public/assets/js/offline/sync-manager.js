window.KriamosSync = (() => {
    const API_SYNC_URL = '/api/sync/formato';

    async function queueForm(form) {
        const formData = new FormData(form);
        const payload = {};

        formData.forEach((value, key) => {
            payload[key] = value;
        });

        await window.KriamosDB.addToQueue({
            tipo: 'formato',
            endpoint: API_SYNC_URL,
            payload,
            source_url: window.location.pathname + window.location.search
        });

        updateBadge();
        alert('No hay conexión. El registro quedó guardado en cola local y se sincronizará cuando vuelva internet.');
    }

    async function syncPending() {
        if (!navigator.onLine) {
            updateBadge();
            return;
        }

        const pending = await window.KriamosDB.allPending();

        for (const item of pending) {
            try {
                const response = await fetch(item.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Kriamos-Offline-Sync': '1'
                    },
                    body: JSON.stringify({
                        local_id: item.local_id,
                        payload: item.payload,
                        tipo: item.tipo,
                        created_at: item.created_at
                    })
                });

                const result = await response.json();

                if (response.ok && result.ok) {
                    await window.KriamosDB.markSynced(item.local_id, result.remote_id || null);
                } else {
                    await window.KriamosDB.markError(item.local_id, result.message || 'Error de sincronización');
                }
            } catch (error) {
                await window.KriamosDB.markError(item.local_id, error.message);
            }
        }

        updateBadge();
    }

    async function updateBadge() {
        const badge = document.getElementById('offlineQueueBadge');
        const status = document.getElementById('connectionStatus');

        if (badge) {
            const count = await window.KriamosDB.countPending();
            badge.textContent = count;
            badge.classList.toggle('d-none', count === 0);
        }

        if (status) {
            status.textContent = navigator.onLine ? 'En línea' : 'Sin conexión';
            status.className = navigator.onLine ? 'connection-online' : 'connection-offline';
        }
    }

    function bindOfflineForms() {
        document.querySelectorAll('form[data-offline-enabled="true"]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (!navigator.onLine) {
                    event.preventDefault();
                    await queueForm(form);
                }
            });
        });
    }

    function registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(() => console.log('Service Worker registrado'))
                .catch((error) => console.warn('No fue posible registrar Service Worker', error));
        }
    }

    function init() {
        registerServiceWorker();
        bindOfflineForms();
        updateBadge();

        window.addEventListener('online', () => {
            updateBadge();
            syncPending();
        });

        window.addEventListener('offline', updateBadge);

        if (navigator.serviceWorker) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'SYNC_QUEUE') {
                    syncPending();
                }
            });
        }

        setInterval(syncPending, 60000);
    }

    return {
        init,
        syncPending,
        updateBadge,
        queueForm
    };
})();

document.addEventListener('DOMContentLoaded', () => {
    if (window.KriamosDB) {
        window.KriamosSync.init();
    }
});
