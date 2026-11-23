<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Alerta;
use App\Models\Auditoria;
use App\Services\AlertService;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\MailNotificationService;
use App\Services\Session;

class AlertaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('alertas.ver');

        $pageTitle = 'Alertas y vencimientos';
        $model = new Alerta();
        $alertas = $model->all();

        $metricas = [
            'pendientes' => $model->pendientes(),
            'criticas' => $model->countByPriority('critica'),
            'altas' => $model->countByPriority('alta'),
            'medias' => $model->countByPriority('media'),
        ];

        require dirname(__DIR__) . '/Views/alertas/index.php';
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('alertas.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $alerta = (new Alerta())->find($id);

        if (!$alerta) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if ($alerta['estado'] === 'pendiente') {
            (new Alerta())->updateEstado($id, 'leida');
        }

        $pageTitle = 'Detalle de alerta';
        require dirname(__DIR__) . '/Views/alertas/show.php';
    }

    public function generar(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('alertas.generar');

        $resultado = (new AlertService())->generarTodas();
        (new Auditoria())->record(Auth::id(), 'alertas', 'generar', 'alertas', null, null, $resultado);

        Session::flash('success', 'Generación de alertas ejecutada correctamente.');
        redirect('/alertas');
        exit;
    }

    public function estado(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('alertas.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/alertas');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        if (!in_array($estado, ['leida','atendida','cerrada'], true)) {
            Session::flash('error', 'Estado de alerta no válido.');
            redirect('/alertas');
            exit;
        }

        (new Alerta())->updateEstado($id, $estado);
        (new Auditoria())->record(Auth::id(), 'alertas', 'cambiar_estado', 'alertas', $id, null, ['estado' => $estado]);

        Session::flash('success', 'Estado de alerta actualizado.');
        redirect('/alertas/show?id=' . $id);
        exit;
    }

    public function enviarCorreos(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('alertas.generar');

        $alertas = (new Alerta())->recientesParaCorreo();
        $mail = new MailNotificationService();

        $enviadas = 0;
        foreach ($alertas as $alerta) {
            if ($mail->sendAlert($alerta)) {
                $enviadas++;
            }
        }

        Session::flash('success', 'Proceso de envío ejecutado. Correos enviados: ' . $enviadas);
        redirect('/alertas');
        exit;
    }
}
