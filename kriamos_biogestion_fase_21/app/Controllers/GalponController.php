<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class GalponController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('galpones.ver');

        $pageTitle = 'Galpones';
        $galpones = (new Galpon())->all();

        require dirname(__DIR__) . '/Views/galpones/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('galpones.crear');

        $pageTitle = 'Nuevo galpón';
        $galpon = null;
        $granjas = (new Granja())->active();
        $action = '/galpones/store';

        require dirname(__DIR__) . '/Views/galpones/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('galpones.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/galpones/create');
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar granja, código, número y capacidad.');
            redirect('/galpones/create');
            exit;
        }

        $id = (new Galpon())->create($data);
        (new Auditoria())->record(Auth::id(), 'galpones', 'crear', 'galpones', $id, null, $data);

        Session::flash('success', 'Galpón creado correctamente.');
        redirect('/galpones');
        exit;
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('galpones.crear');

        $id = (int) ($_GET['id'] ?? 0);
        $galpon = (new Galpon())->find($id);

        if (!$galpon) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar galpón';
        $granjas = (new Granja())->active();
        $action = '/galpones/update?id=' . $id;

        require dirname(__DIR__) . '/Views/galpones/form.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('galpones.crear');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new Galpon();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/galpones/edit?id=' . $id);
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar granja, código, número y capacidad.');
            redirect('/galpones/edit?id=' . $id);
            exit;
        }

        $model->update($id, $data);
        (new Auditoria())->record(Auth::id(), 'galpones', 'editar', 'galpones', $id, $before, $data);

        Session::flash('success', 'Galpón actualizado correctamente.');
        redirect('/galpones');
        exit;
    }

    private function payload(): array
    {
        return [
            'granja_id' => (int) ($_POST['granja_id'] ?? 0),
            'codigo' => strtoupper(trim($_POST['codigo'] ?? '')),
            'numero' => max(0, (int) ($_POST['numero'] ?? 0)),
            'capacidad_pollos' => max(0, (int) ($_POST['capacidad_pollos'] ?? 0)),
            'area_m2' => ($_POST['area_m2'] ?? '') !== '' ? (float) $_POST['area_m2'] : null,
            'estado' => $_POST['estado'] ?? 'activo',
            'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        ];
    }

    private function isValid(array $data): bool
    {
        return $data['granja_id'] > 0
            && $data['codigo'] !== ''
            && $data['numero'] > 0
            && $data['capacidad_pollos'] > 0
            && in_array($data['estado'], ['activo','inactivo','mantenimiento','vacio_sanitario'], true);
    }
}
