<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\Rol;
use App\Models\UsuarioAdmin;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class UsuarioAdminController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.gestionar');

        $pageTitle = 'Usuarios';
        $filters = [
            'q' => $_GET['q'] ?? '',
            'rol_id' => $_GET['rol_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
        ];

        $usuarios = (new UsuarioAdmin())->all($filters);
        $roles = (new Rol())->active();

        require dirname(__DIR__) . '/Views/usuarios/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.gestionar');

        $pageTitle = 'Crear usuario';
        $usuario = null;
        $roles = (new Rol())->active();
        $action = '/admin/usuarios/store';

        require dirname(__DIR__) . '/Views/usuarios/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/usuarios/create');
            exit;
        }

        $password = $_POST['password'] ?? 'Kriamos2026*';
        $data = $this->payload();
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        $data['debe_cambiar_password'] = 1;

        if (!$this->valid($data)) {
            Session::flash('error', 'Debe diligenciar nombres, apellidos, correo y rol.');
            redirect('/admin/usuarios/create');
            exit;
        }

        $id = (new UsuarioAdmin())->create($data);
        (new Auditoria())->record(Auth::id(), 'usuarios', 'crear', 'usuarios', $id, null, ['email' => $data['email']]);

        Session::flash('success', 'Usuario creado correctamente. Contraseña temporal asignada.');
        redirect('/admin/usuarios');
        exit;
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.gestionar');

        $id = (int) ($_GET['id'] ?? 0);
        $usuario = (new UsuarioAdmin())->find($id);

        if (!$usuario) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar usuario';
        $roles = (new Rol())->active();
        $action = '/admin/usuarios/update?id=' . $id;

        require dirname(__DIR__) . '/Views/usuarios/form.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.gestionar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new UsuarioAdmin();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/usuarios/edit?id=' . $id);
            exit;
        }

        $data = $this->payload();
        $model->update($id, $data);

        (new Auditoria())->record(Auth::id(), 'usuarios', 'editar', 'usuarios', $id, $before, $data);
        Session::flash('success', 'Usuario actualizado correctamente.');
        redirect('/admin/usuarios');
        exit;
    }

    public function resetPassword(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.reset_password');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/usuarios');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? 'Kriamos2026*';

        (new UsuarioAdmin())->resetPassword($id, password_hash($password, PASSWORD_DEFAULT));
        (new Auditoria())->record(Auth::id(), 'usuarios', 'reset_password', 'usuarios', $id, null, ['reset' => true]);

        Session::flash('success', 'Contraseña restablecida correctamente.');
        redirect('/admin/usuarios');
        exit;
    }

    public function estado(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('usuarios.bloquear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/admin/usuarios');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? 'activo';

        if (!in_array($estado, ['activo','inactivo','bloqueado'], true)) {
            $estado = 'inactivo';
        }

        (new UsuarioAdmin())->changeEstado($id, $estado);
        (new Auditoria())->record(Auth::id(), 'usuarios', 'cambiar_estado', 'usuarios', $id, null, ['estado' => $estado]);

        Session::flash('success', 'Estado del usuario actualizado.');
        redirect('/admin/usuarios');
        exit;
    }

    private function payload(): array
    {
        return [
            'rol_id' => (int) ($_POST['rol_id'] ?? 0),
            'nombres' => trim($_POST['nombres'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'cargo' => trim($_POST['cargo'] ?? '') ?: null,
            'telefono' => trim($_POST['telefono'] ?? '') ?: null,
            'estado' => $_POST['estado'] ?? 'activo',
        ];
    }

    private function valid(array $data): bool
    {
        return $data['rol_id'] > 0
            && $data['nombres'] !== ''
            && $data['apellidos'] !== ''
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    }
}
