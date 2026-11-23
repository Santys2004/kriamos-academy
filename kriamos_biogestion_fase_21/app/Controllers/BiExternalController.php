<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\BiExternalConnection;
use App\Models\BiExternalDataset;
use App\Models\BiExternalLog;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class BiExternalController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi_externo.ver');

        $pageTitle = 'BI Externo';
        $connections = (new BiExternalConnection())->all();
        $datasets = (new BiExternalDataset())->all();
        $logs = (new BiExternalLog())->recientes();

        $metricas = [
            'conexiones' => count($connections),
            'datasets' => count($datasets),
            'consumos_hoy' => (new BiExternalLog())->countToday(),
        ];

        require dirname(__DIR__) . '/Views/bi_externo/index.php';
    }

    public function createConnection(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi_externo.gestionar');

        $pageTitle = 'Crear conexión BI';
        $connection = null;
        $action = '/bi-externo/conexiones/store';

        require dirname(__DIR__) . '/Views/bi_externo/conexiones/form.php';
    }

    public function storeConnection(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi_externo.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/bi-externo/conexiones/create');
            exit;
        }

        $plainToken = 'bi_' . bin2hex(random_bytes(32));
        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'herramienta' => $_POST['herramienta'] ?? 'otro',
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'contacto_email' => trim($_POST['contacto_email'] ?? '') ?: null,
            'estado' => $_POST['estado'] ?? 'activo',
            'ip_permitidas' => trim($_POST['ip_permitidas'] ?? '') ?: null,
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => substr($plainToken, 0, 10),
            'creado_por' => Auth::id(),
        ];

        $id = (new BiExternalConnection())->create($data);

        $_SESSION['plain_bi_token'] = $plainToken;
        (new Auditoria())->record(Auth::id(), 'bi_externo', 'crear_conexion', 'bi_external_connections', $id, null, ['nombre' => $data['nombre']]);

        Session::flash('success', 'Conexión BI creada. Copie el token, no se volverá a mostrar.');
        redirect('/bi-externo');
        exit;
    }

    public function datasets(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi_externo.ver');

        $pageTitle = 'Datasets BI';
        $datasets = (new BiExternalDataset())->all();

        require dirname(__DIR__) . '/Views/bi_externo/datasets/index.php';
    }
}
