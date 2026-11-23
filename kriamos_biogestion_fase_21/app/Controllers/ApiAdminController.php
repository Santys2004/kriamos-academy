<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\ApiClient;
use App\Models\ApiLog;
use App\Models\ApiToken;
use App\Models\Auditoria;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class ApiAdminController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        $pageTitle = 'API externa segura';
        $clients = (new ApiClient())->all();
        $logs = (new ApiLog())->recientes();
        $metricas = [
            'consumos_hoy' => (new ApiLog())->countToday(),
            'status_2xx' => (new ApiLog())->countByStatusPrefix('2'),
            'status_4xx' => (new ApiLog())->countByStatusPrefix('4'),
            'status_5xx' => (new ApiLog())->countByStatusPrefix('5'),
        ];

        require dirname(__DIR__) . '/Views/api/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        $pageTitle = 'Crear cliente API';
        $client = null;
        $action = '/api-admin/clientes/store';

        require dirname(__DIR__) . '/Views/api/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/api-admin/clientes/create');
            exit;
        }

        $data = $this->payload();
        $data['creado_por'] = Auth::id();

        $id = (new ApiClient())->create($data);
        (new Auditoria())->record(Auth::id(), 'api', 'crear_cliente', 'api_clients', $id, null, $data);

        Session::flash('success', 'Cliente API creado correctamente.');
        redirect('/api-admin/clientes/show?id=' . $id);
        exit;
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new ApiClient();
        $client = $model->find($id);

        if (!$client) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Cliente API';
        $tokens = $model->tokens($id);

        require dirname(__DIR__) . '/Views/api/show.php';
    }

    public function generateToken(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/api-admin');
            exit;
        }

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $expiresAt = ($_POST['expires_at'] ?? '') ?: null;

        $plainToken = 'kriamos_' . bin2hex(random_bytes(32));
        (new ApiToken())->create($clientId, $plainToken, $expiresAt, Auth::id());

        $_SESSION['plain_api_token'] = $plainToken;
        (new Auditoria())->record(Auth::id(), 'api', 'generar_token', 'api_tokens', $clientId, null, ['client_id' => $clientId]);

        Session::flash('success', 'Token generado. Copie el token ahora, no volverá a mostrarse.');
        redirect('/api-admin/clientes/show?id=' . $clientId);
        exit;
    }

    public function revokeToken(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('api.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/api-admin');
            exit;
        }

        $tokenId = (int) ($_POST['token_id'] ?? 0);
        $clientId = (int) ($_POST['client_id'] ?? 0);

        (new ApiToken())->revoke($tokenId);
        (new Auditoria())->record(Auth::id(), 'api', 'revocar_token', 'api_tokens', $tokenId, null, ['client_id' => $clientId]);

        Session::flash('success', 'Token revocado correctamente.');
        redirect('/api-admin/clientes/show?id=' . $clientId);
        exit;
    }

    private function payload(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'contacto_email' => trim($_POST['contacto_email'] ?? '') ?: null,
            'estado' => $_POST['estado'] ?? 'activo',
            'ip_permitidas' => trim($_POST['ip_permitidas'] ?? '') ?: null,
        ];
    }
}
