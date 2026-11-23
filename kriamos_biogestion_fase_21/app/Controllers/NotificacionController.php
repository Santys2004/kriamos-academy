<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Alerta;
use App\Models\MailQueue;
use App\Models\Notificacion;
use App\Models\PushSubscription;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Mail\MailQueueService;
use App\Services\NotificationService;
use App\Services\Session;

class NotificacionController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.ver');

        $pageTitle = 'Centro de notificaciones';
        $model = new Notificacion();

        $notificaciones = $model->all(Auth::id());
        $metricas = [
            'pendientes' => $model->countByEstado('pendiente'),
            'enviadas' => $model->countByEstado('enviada'),
            'leidas' => $model->countByEstado('leida'),
            'push_activas' => (new PushSubscription())->countActive(),
        ];

        require dirname(__DIR__) . '/Views/notificaciones/index.php';
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $notificacion = (new Notificacion())->find($id);

        if (!$notificacion) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        (new Notificacion())->marcarLeida($id);

        $pageTitle = 'Detalle de notificación';
        require dirname(__DIR__) . '/Views/notificaciones/show.php';
    }

    public function mailQueue(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.gestionar');

        $pageTitle = 'Cola de correos';
        $queue = new MailQueue();

        $correos = $queue->recientes();
        $metricas = [
            'pendientes' => $queue->countByEstado('pendiente'),
            'enviados' => $queue->countByEstado('enviado'),
            'errores' => $queue->countByEstado('error'),
        ];

        require dirname(__DIR__) . '/Views/correos/queue.php';
    }

    public function procesarCola(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.gestionar');

        $result = (new MailQueueService())->process(50);

        Session::flash('success', 'Cola procesada. Enviados: ' . $result['enviados'] . '. Errores: ' . $result['errores']);
        redirect('/notificaciones/correos');
        exit;
    }

    public function generarDesdeAlertas(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.gestionar');

        $alertas = (new Alerta())->all();
        $service = new NotificationService();
        $creadas = 0;

        foreach ($alertas as $alerta) {
            if (empty($alerta['usuario_id'])) {
                continue;
            }

            $id = $service->createInternal([
                'usuario_id' => $alerta['usuario_id'],
                'alerta_id' => $alerta['id'],
                'titulo' => $alerta['titulo'],
                'mensaje' => $alerta['mensaje'],
                'prioridad' => $alerta['prioridad'],
                'url_accion' => '/alertas/show?id=' . $alerta['id'],
            ]);

            if (!empty($alerta['usuario_nombres']) || !empty($alerta['usuario_apellidos'])) {
                // La cola de correo requiere destinatario; si la vista de alertas no trae email,
                // se mantiene solo notificación interna.
            }

            if ($id) {
                $creadas++;
            }
        }

        Session::flash('success', 'Notificaciones internas generadas desde alertas: ' . $creadas);
        redirect('/notificaciones');
        exit;
    }

    public function pushPanel(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.ver');

        $pageTitle = 'Notificaciones push';
        require dirname(__DIR__) . '/Views/push/index.php';
    }

    public function savePushSubscription(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('notificaciones.ver');

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $subscription = json_decode($raw, true);

        if (!$subscription || empty($subscription['endpoint'])) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Suscripción inválida.']);
            return;
        }

        (new PushSubscription())->saveSubscription(Auth::id(), $subscription);

        echo json_encode(['ok' => true, 'message' => 'Suscripción push guardada.']);
    }
}
