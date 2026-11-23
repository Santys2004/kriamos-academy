<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\AyudaArticulo;
use App\Models\ChatAyuda;
use App\Models\ManualPaso;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class AyudaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        $pageTitle = 'Centro de ayuda';
        $filters = [
            'q' => $_GET['q'] ?? '',
            'modulo' => $_GET['modulo'] ?? '',
        ];

        $model = new AyudaArticulo();
        $articulos = $model->all($filters);
        $modulos = $model->modules();
        $totalArticulos = $model->count();

        require dirname(__DIR__) . '/Views/ayuda/index.php';
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $articulo = (new AyudaArticulo())->find($id);

        if (!$articulo) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = $articulo['titulo'];
        require dirname(__DIR__) . '/Views/ayuda/show.php';
    }

    public function manual(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        $pageTitle = 'Manual interactivo';
        $model = new ManualPaso();
        $modulos = $model->modules();
        $modulo = $_GET['modulo'] ?? ($modulos[0] ?? 'inicio');
        $pasos = $model->byModulo($modulo);

        require dirname(__DIR__) . '/Views/manual/index.php';
    }

    public function chat(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        $pageTitle = 'Chat de ayuda';
        $logs = (new ChatAyuda())->recientes();

        require dirname(__DIR__) . '/Views/chat/index.php';
    }

    public function chatAsk(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        header('Content-Type: application/json; charset=utf-8');

        $pregunta = trim($_POST['pregunta'] ?? '');

        if ($pregunta === '') {
            echo json_encode(['ok' => false, 'message' => 'Debe escribir una pregunta.']);
            return;
        }

        $chat = new ChatAyuda();
        $base = $chat->buscarRespuesta($pregunta);

        if ($base) {
            $respuesta = $base['respuesta'];
            $baseId = (int) $base['id'];
        } else {
            $respuesta = 'No encontré una respuesta exacta en la base de conocimiento. Revise el manual interactivo o consulte al administrador del sistema para agregar esta pregunta frecuente.';
            $baseId = null;
        }

        $logId = $chat->registrarPregunta(Auth::id(), $pregunta, $respuesta, $baseId);

        echo json_encode([
            'ok' => true,
            'respuesta' => $respuesta,
            'log_id' => $logId,
            'fuente' => $base ? ($base['pregunta'] ?? 'Base de conocimiento') : 'Sin coincidencia exacta',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function chatRate(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ayuda.ver');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/ayuda/chat');
            exit;
        }

        $logId = (int) ($_POST['log_id'] ?? 0);
        $util = (int) ($_POST['util'] ?? 0);

        if ($logId > 0) {
            (new ChatAyuda())->calificar($logId, $util === 1 ? 1 : 0);
        }

        Session::flash('success', 'Gracias por calificar la respuesta.');
        redirect('/ayuda/chat');
        exit;
    }
}
