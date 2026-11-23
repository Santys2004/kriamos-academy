<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\ParametroSistema;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class ParametroController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('parametros.gestionar');

        $pageTitle = 'Parámetros del sistema';
        $grupo = $_GET['grupo'] ?? null;
        $model = new ParametroSistema();
        $parametros = $model->all($grupo);
        $grupos = $model->groups();

        require dirname(__DIR__) . '/Views/parametros/index.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('parametros.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/parametros');
            exit;
        }

        $parametros = $_POST['parametros'] ?? [];
        $model = new ParametroSistema();

        foreach ($parametros as $id => $valor) {
            $model->updateValue((int) $id, trim((string) $valor));
        }

        (new Auditoria())->record(Auth::id(), 'parametros', 'actualizar', 'parametros_sistema', null, null, ['total' => count($parametros)]);
        Session::flash('success', 'Parámetros actualizados correctamente.');
        redirect('/admin/parametros');
        exit;
    }
}
