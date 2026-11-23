<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\CicloProductivo;
use App\Models\FaseCiclo;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class CicloController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ciclos.ver');

        $pageTitle = 'Ciclos productivos';
        $ciclos = (new CicloProductivo())->all();

        require dirname(__DIR__) . '/Views/ciclos/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ciclos.crear');

        $pageTitle = 'Nuevo ciclo productivo';
        $ciclo = null;
        $granjas = (new Granja())->active();
        $galpones = (new Galpon())->all();
        $fases = (new FaseCiclo())->active();
        $action = '/ciclos/store';

        require dirname(__DIR__) . '/Views/ciclos/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ciclos.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/ciclos/create');
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar granja, galpón, fase, código y fecha de inicio.');
            redirect('/ciclos/create');
            exit;
        }

        $id = (new CicloProductivo())->create($data);
        (new Auditoria())->record(Auth::id(), 'ciclos', 'crear', 'ciclos_productivos', $id, null, $data);

        Session::flash('success', 'Ciclo productivo creado correctamente.');
        redirect('/ciclos');
        exit;
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ciclos.crear');

        $id = (int) ($_GET['id'] ?? 0);
        $ciclo = (new CicloProductivo())->find($id);

        if (!$ciclo) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar ciclo productivo';
        $granjas = (new Granja())->active();
        $galpones = (new Galpon())->all();
        $fases = (new FaseCiclo())->active();
        $action = '/ciclos/update?id=' . $id;

        require dirname(__DIR__) . '/Views/ciclos/form.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ciclos.crear');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new CicloProductivo();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/ciclos/edit?id=' . $id);
            exit;
        }

        $data = $this->payload();

        if (!$this->isValid($data)) {
            Session::flash('error', 'Debe diligenciar granja, galpón, fase, código y fecha de inicio.');
            redirect('/ciclos/edit?id=' . $id);
            exit;
        }

        $model->update($id, $data);
        (new Auditoria())->record(Auth::id(), 'ciclos', 'editar', 'ciclos_productivos', $id, $before, $data);

        Session::flash('success', 'Ciclo productivo actualizado correctamente.');
        redirect('/ciclos');
        exit;
    }

    private function payload(): array
    {
        return [
            'granja_id' => (int) ($_POST['granja_id'] ?? 0),
            'galpon_id' => (int) ($_POST['galpon_id'] ?? 0),
            'fase_ciclo_id' => (int) ($_POST['fase_ciclo_id'] ?? 0),
            'codigo' => strtoupper(trim($_POST['codigo'] ?? '')),
            'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
            'fecha_encasetamiento' => ($_POST['fecha_encasetamiento'] ?? '') ?: null,
            'fecha_estimada_salida' => ($_POST['fecha_estimada_salida'] ?? '') ?: null,
            'fecha_cierre' => ($_POST['fecha_cierre'] ?? '') ?: null,
            'edad_aves_dias' => max(0, (int) ($_POST['edad_aves_dias'] ?? 0)),
            'linea_genetica' => trim($_POST['linea_genetica'] ?? '') ?: null,
            'aves_recibidas' => max(0, (int) ($_POST['aves_recibidas'] ?? 0)),
            'aves_actuales' => max(0, (int) ($_POST['aves_actuales'] ?? 0)),
            'mortalidad_acumulada' => max(0, (int) ($_POST['mortalidad_acumulada'] ?? 0)),
            'estado' => $_POST['estado'] ?? 'activo',
            'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        ];
    }

    private function isValid(array $data): bool
    {
        return $data['granja_id'] > 0
            && $data['galpon_id'] > 0
            && $data['fase_ciclo_id'] > 0
            && $data['codigo'] !== ''
            && $data['fecha_inicio'] !== ''
            && in_array($data['estado'], ['activo','cerrado','suspendido'], true);
    }
}
