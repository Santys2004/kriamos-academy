<?php

namespace App\Services\Push;

use App\Models\PushSubscription;

class PushNotificationService
{
    public function notifyUser(int $usuarioId, string $title, string $body, string $url = '/'): array
    {
        $subscriptions = (new PushSubscription())->activeByUsuario($usuarioId);

        // Base técnica: se registra la intención de push.
        // Para Web Push real se requiere VAPID y firma del payload.
        // Se recomienda integrar minishlink/web-push mediante Composer en producción.
        return [
            'ok' => true,
            'subscriptions' => count($subscriptions),
            'message' => 'Push preparado. Requiere implementación VAPID para envío real.',
            'payload' => [
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ],
        ];
    }
}
