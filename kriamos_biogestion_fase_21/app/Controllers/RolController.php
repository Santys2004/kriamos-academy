<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\Rol;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class RolController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('roles.gestionar');

        $pageTitle = 'Roles y permisos';
        $roles = (new Rol())->all();

        require dirname(__DIR__) . '/Views/roles/index.php';
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('roles.gestionar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new Rol();
        $rol = $model->find($id);

        if (!$rol) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar rol';
        $permisos = $model->permissions($id);

        require dirname(__DIR__) . '/Views/roles/edit.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('roles.gestionar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new Rol();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/roles/edit?id=' . $id);
            exit;
        }

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        $model->update($id, $data);
        $model->syncPermissions($id, $_POST['permisos'] ?? []);

        (new Auditoria())->record(Auth::id(), 'roles', 'editar_permisos', 'roles', $id, $before, $data);
        Session::flash('success', 'Rol y permisos actualizados correctamente.');
        redirect('/admin/roles');
        exit;
    }
}
