<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\Granja;
use App\Models\IcaExpediente;
use App\Models\IcaExportacion;
use App\Models\IcaHallazgo;
use App\Models\IcaMatrizDocumento;
use App\Models\IcaPlanAccion;
use App\Models\UsuarioAdmin;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\ExportService;
use App\Services\IcaAudit\IcaMatrixBuilderService;
use App\Services\IcaAudit\IcaPackageService;
use App\Services\Session;

class AuditoriaIcaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.ver');

        $pageTitle = 'Auditoría ICA';
        $filters = [
            'granja_id' => $_GET['granja_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ];

        $expedientes = (new IcaExpediente())->all($filters);
        $granjas = (new Granja())->active();

        $metricas = [
            'borrador' => (new IcaExpediente())->countByEstado('borrador'),
            'preparacion' => (new IcaExpediente())->countByEstado('preparacion'),
            'cerrado' => (new IcaExpediente())->countByEstado('cerrado'),
            'hallazgos_criticos' => (new IcaHallazgo())->countByCriticidad('critica'),
            'planes_vencidos' => (new IcaPlanAccion())->countVencidos(),
        ];

        require dirname(__DIR__) . '/Views/auditoria_ica/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.gestionar');

        $pageTitle = 'Crear expediente ICA';
        $granjas = (new Granja())->active();

        require dirname(__DIR__) . '/Views/auditoria_ica/expedientes/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/auditoria-ica/create');
            exit;
        }

        $granjaId = (int) ($_POST['granja_id'] ?? 0);
        $codigo = 'ICA-' . date('YmdHis') . '-G' . $granjaId;

        $id = (new IcaExpediente())->create([
            'codigo' => $codigo,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'granja_id' => $granjaId,
            'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_fin' => $_POST['fecha_fin'] ?? null,
            'objetivo' => trim($_POST['objetivo'] ?? '') ?: null,
            'alcance' => trim($_POST['alcance'] ?? '') ?: null,
            'estado' => 'borrador',
            'creado_por' => Auth::id(),
        ]);

        $matrix = (new IcaMatrixBuilderService())->buildDefaultMatrix($id, $granjaId);

        (new Auditoria())->record(Auth::id(), 'auditoria_ica', 'crear_expediente', 'ica_expedientes', $id, null, ['matriz_items' => $matrix['created']]);

        Session::flash('success', 'Expediente ICA creado con matriz documental inicial.');
        redirect('/auditoria-ica/show?id=' . $id);
        exit;
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $expediente = (new IcaExpediente())->find($id);

        if (!$expediente) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Expediente ICA';
        $matriz = (new IcaMatrizDocumento())->byExpediente($id);
        $resumen = (new IcaMatrizDocumento())->resumenCumplimiento($id);
        $hallazgos = (new IcaHallazgo())->byExpediente($id);
        $planes = (new IcaPlanAccion())->byExpediente($id);
        $exportaciones = (new IcaExportacion())->byExpediente($id);
        $usuarios = (new UsuarioAdmin())->all();

        require dirname(__DIR__) . '/Views/auditoria_ica/show.php';
    }

    public function updateMatriz(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/auditoria-ica');
            exit;
        }

        $id = (int) ($_POST['matriz_id'] ?? 0);
        $expedienteId = (int) ($_POST['expediente_id'] ?? 0);
        $estado = $_POST['estado_cumplimiento'] ?? 'pendiente';
        $observacion = trim($_POST['observacion'] ?? '') ?: null;

        (new IcaMatrizDocumento())->updateCumplimiento($id, $estado, $observacion);

        (new Auditoria())->record(Auth::id(), 'auditoria_ica', 'actualizar_matriz', 'ica_matriz_documental', $id, null, ['estado' => $estado]);

        Session::flash('success', 'Matriz documental actualizada.');
        redirect('/auditoria-ica/show?id=' . $expedienteId);
        exit;
    }

    public function storeHallazgo(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/auditoria-ica');
            exit;
        }

        $expedienteId = (int) ($_POST['expediente_id'] ?? 0);

        $id = (new IcaHallazgo())->create([
            'expediente_id' => $expedienteId,
            'matriz_documental_id' => ($_POST['matriz_documental_id'] ?? '') !== '' ? (int) $_POST['matriz_documental_id'] : null,
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'criticidad' => $_POST['criticidad'] ?? 'media',
            'estado' => 'abierto',
            'evidencia_path' => null,
            'reportado_por' => Auth::id(),
        ]);

        (new Auditoria())->record(Auth::id(), 'auditoria_ica', 'crear_hallazgo', 'ica_hallazgos', $id, null, ['expediente_id' => $expedienteId]);

        Session::flash('success', 'Hallazgo registrado.');
        redirect('/auditoria-ica/show?id=' . $expedienteId);
        exit;
    }

    public function storePlan(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/auditoria-ica');
            exit;
        }

        $expedienteId = (int) ($_POST['expediente_id'] ?? 0);

        $id = (new IcaPlanAccion())->create([
            'expediente_id' => $expedienteId,
            'hallazgo_id' => ($_POST['hallazgo_id'] ?? '') !== '' ? (int) $_POST['hallazgo_id'] : null,
            'accion' => trim($_POST['accion'] ?? ''),
            'responsable_id' => ($_POST['responsable_id'] ?? '') !== '' ? (int) $_POST['responsable_id'] : null,
            'fecha_compromiso' => $_POST['fecha_compromiso'] ?? date('Y-m-d'),
            'estado' => 'abierto',
            'avance_porcentaje' => 0,
            'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        ]);

        (new Auditoria())->record(Auth::id(), 'auditoria_ica', 'crear_plan_accion', 'ica_planes_accion', $id, null, ['expediente_id' => $expedienteId]);

        Session::flash('success', 'Plan de acción creado.');
        redirect('/auditoria-ica/show?id=' . $expedienteId);
        exit;
    }

    public function generarPaquete(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.exportar');

        $id = (int) ($_GET['id'] ?? 0);
        $result = (new IcaPackageService())->generate($id);

        if (!$result['ok']) {
            Session::flash('error', $result['message']);
        } else {
            (new Auditoria())->record(Auth::id(), 'auditoria_ica', 'generar_paquete', 'ica_exportaciones', $result['exportacion_id'], null, ['hash' => $result['hash']]);
            Session::flash('success', $result['message']);
        }

        redirect('/auditoria-ica/show?id=' . $id);
        exit;
    }

    public function csv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria_ica.exportar');

        $id = (int) ($_GET['id'] ?? 0);
        $rows = (new IcaMatrizDocumento())->byExpediente($id);

        ExportService::csv('matriz_ica_expediente_' . $id . '_' . date('Ymd_His') . '.csv', [
            'categoria' => 'Categoría',
            'requisito' => 'Requisito',
            'descripcion' => 'Descripción',
            'estado_cumplimiento' => 'Estado',
            'obligatorio' => 'Obligatorio',
            'observacion' => 'Observación',
        ], $rows);
    }
}
