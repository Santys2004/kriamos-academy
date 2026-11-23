<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\CicloProductivo;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class GranjaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.ver');

        $pageTitle = 'Granjas';
        $granjas = (new Granja())->all();

        require dirname(__DIR__) . '/Views/granjas/index.php';
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $granja = (new Granja())->find($id);

        if (!$granja) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Detalle de granja';
        $galpones = (new Galpon())->byGranja($id);
        $ciclos = (new CicloProductivo())->byGranja($id);

        require dirname(__DIR__) . '/Views/granjas/show.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.crear');

        $pageTitle = 'Nueva granja';
        $granja = null;
        $action = '/granjas/store';

        require dirname(__DIR__) . '/Views/granjas/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/granjas/create');
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar nombre, sigla, capacidad y número de galpones.');
            redirect('/granjas/create');
            exit;
        }

        $id = (new Granja())->create($data);
        (new Auditoria())->record(Auth::id(), 'granjas', 'crear', 'granjas', $id, null, $data);

        Session::flash('success', 'Granja creada correctamente.');
        redirect('/granjas/show?id=' . $id);
        exit;
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.editar');

        $id = (int) ($_GET['id'] ?? 0);
        $granja = (new Granja())->find($id);

        if (!$granja) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar granja';
        $action = '/granjas/update?id=' . $id;

        require dirname(__DIR__) . '/Views/granjas/form.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('granjas.editar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new Granja();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/granjas/edit?id=' . $id);
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar nombre, sigla, capacidad y número de galpones.');
            redirect('/granjas/edit?id=' . $id);
            exit;
        }

        $model->update($id, $data);
        (new Auditoria())->record(Auth::id(), 'granjas', 'editar', 'granjas', $id, $before, $data);

        Session::flash('success', 'Granja actualizada correctamente.');
        redirect('/granjas/show?id=' . $id);
        exit;
    }

    private function payload(): array
    {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'sigla' => strtoupper(trim($_POST['sigla'] ?? '')),
            'codigo_interno' => trim($_POST['codigo_interno'] ?? '') ?: null,
            'ubicacion' => trim($_POST['ubicacion'] ?? '') ?: null,
            'municipio' => trim($_POST['municipio'] ?? '') ?: null,
            'departamento' => trim($_POST['departamento'] ?? '') ?: null,
            'capacidad_pollos' => max(0, (int) ($_POST['capacidad_pollos'] ?? 0)),
            'numero_galpones' => max(0, (int) ($_POST['numero_galpones'] ?? 0)),
            'estado' => $_POST['estado'] ?? 'activa',
            'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        ];
    }

    private function isValid(array $data): bool
    {
        return $data['nombre'] !== ''
            && $data['sigla'] !== ''
            && $data['capacidad_pollos'] > 0
            && $data['numero_galpones'] > 0
            && in_array($data['estado'], ['activa','inactiva','suspendida','en_auditoria'], true);
    }
}
