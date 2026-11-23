(function () {
    'use strict';

    const MATERIAL_CACHE = 'kriamos-offline-materials-v1';
    let deferredPrompt = null;

    function qs(selector, root = document) {
        return root.querySelector(selector);
    }

    function qsa(selector, root = document) {
        return Array.from(root.querySelectorAll(selector));
    }

    function showConnectionStatus() {
        const banner = qs('#connectionStatus');
        if (!banner) return;

        const online = navigator.onLine;
        banner.className = 'connection-status ' + (online ? 'online' : 'offline');
        banner.textContent = online
            ? 'Conexión restablecida. La plataforma puede sincronizar datos.'
            : 'Sin conexión. Solo podrá consultar contenidos previamente descargados.';

        if (online) {
            setTimeout(() => banner.classList.add('d-none'), 3500);
        } else {
            banner.classList.remove('d-none');
        }
    }

    function initInstallPrompt() {
        const buttons = qsa('#pwaInstallButton, #pwaInstallButtonSidebar');
        window.addEventListener('beforeinstallprompt', event => {
            event.preventDefault();
            deferredPrompt = event;
            buttons.forEach(btn => btn.classList.remove('d-none'));
        });

        buttons.forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                buttons.forEach(button => button.classList.add('d-none'));
            });
        });
    }

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        try {
            await navigator.serviceWorker.register('service-worker.js');
        } catch (error) {
            console.warn('No fue posible registrar el Service Worker:', error);
        }
    }

    function setMaterialStatus(materialId, message, type = 'muted') {
        const status = qs(`[data-material-status="${materialId}"]`);
        if (!status) return;
        status.className = `offline-material-status small mt-2 text-${type}`;
        status.textContent = message;
    }

    async function isMaterialCached(url) {
        if (!('caches' in window)) return false;
        const cache = await caches.open(MATERIAL_CACHE);
        const requestUrl = new URL(url, window.location.href).toString();
        const cached = await cache.match(requestUrl);
        return Boolean(cached);
    }

    async function refreshMaterialStatuses() {
        const buttons = qsa('.offline-download-btn');
        for (const button of buttons) {
            const id = button.dataset.materialId;
            const url = button.dataset.url;
            if (!id || !url) continue;
            if (await isMaterialCached(url)) {
                setMaterialStatus(id, 'Estado local: descargado en este dispositivo.', 'success');
                button.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>Actualizar descarga offline';
            }
        }
    }

    async function cacheMaterial(button) {
        if (!('caches' in window)) {
            alert('Este navegador no soporta almacenamiento offline con Cache API.');
            return;
        }

        const url = button.dataset.url;
        const title = button.dataset.title || 'material';
        const materialId = button.dataset.materialId;
        const documentNumber = button.dataset.document;
        const absoluteUrl = new URL(url, window.location.href).toString();

        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Descargando...';
        setMaterialStatus(materialId, `Descargando: ${title}`, 'muted');

        try {
            const response = await fetch(absoluteUrl, { cache: 'reload' });
            if (!response.ok) {
                throw new Error('No fue posible descargar el archivo.');
            }

            const cache = await caches.open(MATERIAL_CACHE);
            await cache.put(absoluteUrl, response.clone());

            setMaterialStatus(materialId, 'Estado local: descargado en este dispositivo.', 'success');
            button.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i>Descargado / actualizar';

            if (navigator.onLine && documentNumber && materialId) {
                const form = new FormData();
                form.append('document_number', documentNumber);
                form.append('material_id', materialId);
                form.append('device_info', navigator.userAgent || 'PWA');
                fetch('index.php?page=registrar_descarga_offline', {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin'
                }).catch(() => {});
            }
        } catch (error) {
            console.error(error);
            setMaterialStatus(materialId, 'No se pudo descargar. Revise la conexión e intente nuevamente.', 'danger');
            button.innerHTML = originalText;
        } finally {
            button.disabled = false;
        }
    }

    async function clearOfflineMaterials() {
        if (!('caches' in window)) return;
        if (!confirm('¿Desea eliminar los materiales descargados localmente en este dispositivo?')) return;
        await caches.delete(MATERIAL_CACHE);
        qsa('.offline-material-status').forEach(el => {
            el.className = 'offline-material-status small mt-2 text-muted';
            el.textContent = 'Estado local: pendiente de descarga.';
        });
        qsa('.offline-download-btn').forEach(btn => {
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down me-2"></i>Descargar para offline';
        });
        alert('Materiales offline eliminados de este dispositivo.');
    }

    function initOfflineButtons() {
        qsa('.offline-download-btn').forEach(button => {
            button.addEventListener('click', () => cacheMaterial(button));
        });
        qsa('[data-clear-offline-cache]').forEach(button => {
            button.addEventListener('click', clearOfflineMaterials);
        });
        refreshMaterialStatuses();
    }

    document.addEventListener('DOMContentLoaded', () => {
        registerServiceWorker();
        initInstallPrompt();
        initOfflineButtons();
        showConnectionStatus();
    });

    window.addEventListener('online', showConnectionStatus);
    window.addEventListener('offline', showConnectionStatus);
})();
