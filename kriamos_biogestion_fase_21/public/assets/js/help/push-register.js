document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('enablePushBtn');
    const status = document.getElementById('pushStatus');

    if (!btn) return;

    btn.addEventListener('click', async () => {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            status.textContent = 'Este navegador no soporta push notifications.';
            return;
        }

        try {
            const permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                status.textContent = 'Permiso de notificaciones no concedido.';
                return;
            }

            const registration = await navigator.serviceWorker.ready;

            // Suscripción técnica de base. Para Web Push real falta VAPID.
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                status.textContent = 'El navegador requiere clave pública VAPID para suscripción real. Se registrará disponibilidad del dispositivo.';
                subscription = {
                    endpoint: 'pending-vapid-' + crypto.randomUUID(),
                    keys: { p256dh: '', auth: '' }
                };
            } else {
                subscription = subscription.toJSON();
            }

            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });

            const result = await response.json();
            status.textContent = result.message || 'Solicitud procesada.';
        } catch (error) {
            status.textContent = 'Error registrando push: ' + error.message;
        }
    });
});
