window.KriamosDB = (() => {
    const DB_NAME = 'kriamos_biogestion_ica';
    const DB_VERSION = 1;
    const QUEUE_STORE = 'sync_queue';
    const CACHE_STORE = 'offline_cache';

    function open() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                if (!db.objectStoreNames.contains(QUEUE_STORE)) {
                    const queue = db.createObjectStore(QUEUE_STORE, { keyPath: 'local_id' });
                    queue.createIndex('estado', 'estado', { unique: false });
                    queue.createIndex('created_at', 'created_at', { unique: false });
                }

                if (!db.objectStoreNames.contains(CACHE_STORE)) {
                    db.createObjectStore(CACHE_STORE, { keyPath: 'key' });
                }
            };

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function addToQueue(record) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE_STORE, 'readwrite');
            tx.objectStore(QUEUE_STORE).put({
                ...record,
                local_id: record.local_id || crypto.randomUUID(),
                estado: 'pendiente',
                intentos: record.intentos || 0,
                created_at: record.created_at || new Date().toISOString(),
                updated_at: new Date().toISOString()
            });
            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function allPending() {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE_STORE, 'readonly');
            const request = tx.objectStore(QUEUE_STORE).getAll();
            request.onsuccess = () => resolve(request.result.filter(item => item.estado !== 'sincronizado'));
            request.onerror = () => reject(request.error);
        });
    }

    async function markSynced(localId, remoteId) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE_STORE, 'readwrite');
            const store = tx.objectStore(QUEUE_STORE);
            const get = store.get(localId);

            get.onsuccess = () => {
                const item = get.result;
                if (item) {
                    item.estado = 'sincronizado';
                    item.remote_id = remoteId || null;
                    item.updated_at = new Date().toISOString();
                    store.put(item);
                }
            };

            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function markError(localId, message) {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(QUEUE_STORE, 'readwrite');
            const store = tx.objectStore(QUEUE_STORE);
            const get = store.get(localId);

            get.onsuccess = () => {
                const item = get.result;
                if (item) {
                    item.estado = 'error';
                    item.error = message;
                    item.intentos = (item.intentos || 0) + 1;
                    item.updated_at = new Date().toISOString();
                    store.put(item);
                }
            };

            tx.oncomplete = () => resolve(true);
            tx.onerror = () => reject(tx.error);
        });
    }

    async function countPending() {
        const pending = await allPending();
        return pending.length;
    }

    return {
        open,
        addToQueue,
        allPending,
        markSynced,
        markError,
        countPending
    };
})();
