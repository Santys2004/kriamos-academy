<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Services\Mail\MailQueueService;
use App\Services\Mail\MailTemplateRenderer;

class NotificationService
{
    public function createInternal(array $data): int
    {
        return (new Notificacion())->create([
            'usuario_id' => $data['usuario_id'] ?? null,
            'alerta_id' => $data['alerta_id'] ?? null,
            'canal' => $data['canal'] ?? 'app',
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'prioridad' => $data['prioridad'] ?? 'media',
            'estado' => 'pendiente',
            'url_accion' => $data['url_accion'] ?? null,
        ]);
    }

    public function notifyByEmail(array $data): int
    {
        $renderer = new MailTemplateRenderer();
        $mail = $renderer->render($data['template'] ?? 'alerta_general', [
            'titulo' => $data['titulo'],
            'mensaje' => nl2br(htmlspecialchars($data['mensaje'], ENT_QUOTES, 'UTF-8')),
            'url' => $data['url_accion'] ?? '/',
            'empresa' => 'Kriamos',
        ]);

        return (new MailQueueService())->enqueue([
            'usuario_id' => $data['usuario_id'] ?? null,
            'alerta_id' => $data['alerta_id'] ?? null,
            'notificacion_id' => $data['notificacion_id'] ?? null,
            'destinatario' => $data['destinatario'],
            'asunto' => $mail['asunto'],
            'cuerpo_html' => $mail['cuerpo_html'],
            'prioridad' => $data['prioridad'] ?? 'media',
        ]);
    }
}
