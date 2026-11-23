<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\CicloProductivo;
use App\Models\EstadoDocumental;
use App\Models\FormatoRegistro;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\TipoFormato;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\FormatoTemplate;
use App\Services\FormatoValidationService;
use App\Models\IncidenciaCalidad;
use App\Services\Session;

class FormatoController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.ver');

        $pageTitle = 'Formatos ICA 3652';
        $registros = (new FormatoRegistro())->all();
        $totalRegistros = (new FormatoRegistro())->count();
        $registrosHoy = (new FormatoRegistro())->countToday();
        $noAplica = (new FormatoRegistro())->countNoAplica();

        require dirname(__DIR__) . '/Views/formatos/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.crear');

        $pageTitle = 'Nuevo formato';
        $tipos = (new TipoFormato())->all();
        $granjas = (new Granja())->active();
        $galpones = (new Galpon())->all();
        $ciclos = (new CicloProductivo())->all();

        $tipoId = (int) ($_GET['tipo_formato_id'] ?? 0);
        $tipoSeleccionado = $tipoId ? (new TipoFormato())->find($tipoId) : null;
        $fields = $tipoSeleccionado ? FormatoTemplate::fields($tipoSeleccionado['codigo']) : [];
        $guide = $tipoSeleccionado ? FormatoTemplate::guide($tipoSeleccionado['codigo']) : null;

        require dirname(__DIR__) . '/Views/formatos/create.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/formatos/create');
            exit;
        }

        $tipoId = (int) ($_POST['tipo_formato_id'] ?? 0);
        $granjaId = (int) ($_POST['granja_id'] ?? 0);
        $tipo = (new TipoFormato())->find($tipoId);
        $granja = (new Granja())->find($granjaId);

        if (!$tipo || !$granja) {
            Session::flash('error', 'Debe seleccionar un tipo de formato y una granja válida.');
            redirect('/formatos/create');
            exit;
        }

        $esNoAplica = isset($_POST['es_no_aplica']) ? 1 : 0;
        $motivoNoAplica = trim($_POST['motivo_no_aplica'] ?? '');

        if ($esNoAplica === 1 && $motivoNoAplica === '') {
            Session::flash('error', 'Cuando seleccione No aplica, debe registrar el motivo.');
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        if ((int) $tipo['permite_no_aplica'] === 0 && $esNoAplica === 1) {
            Session::flash('error', 'Este formato no permite marcar No aplica.');
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        if ((int) $tipo['requiere_galpon'] === 1 && (int) ($_POST['galpon_id'] ?? 0) <= 0 && $esNoAplica === 0) {
            Session::flash('error', 'Este formato requiere seleccionar un galpón.');
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        if ((int) $tipo['requiere_ciclo'] === 1 && (int) ($_POST['ciclo_id'] ?? 0) <= 0 && $esNoAplica === 0) {
            Session::flash('error', 'Este formato requiere seleccionar un ciclo productivo.');
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        $fields = FormatoTemplate::fields($tipo['codigo']);
        $datos = FormatoTemplate::normalizePostData($_POST, $fields);

        foreach ($fields as $field) {
            if (($field['required'] ?? false) && $esNoAplica === 0) {
                $value = $datos[$field['name']] ?? null;
                if ($value === null || $value === '') {
                    Session::flash('error', 'Debe diligenciar el campo obligatorio: ' . $field['label']);
                    redirect('/formatos/create?tipo_formato_id=' . $tipoId);
                    exit;
                }
            }
        }


        $validation = (new FormatoValidationService())->validateBeforeSave([
            'tipo_formato_id' => $tipoId,
            'tipo_codigo' => $tipo['codigo'],
            'granja_id' => $granjaId,
            'galpon_id' => ((int) ($_POST['galpon_id'] ?? 0)) ?: null,
            'ciclo_id' => ((int) ($_POST['ciclo_id'] ?? 0)) ?: null,
            'fecha_formato' => $_POST['fecha_formato'] ?? date('Y-m-d'),
            'es_no_aplica' => $esNoAplica,
        ], $datos);

        if (!empty($validation['errors'])) {
            Session::flash('error', implode(' ', $validation['errors']));
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        $spellingWarnings = (new FormatoValidationService())->possibleSpellingWarnings($datos);

        $estadoCodigo = $esNoAplica ? 'NO_APLICA' : 'DILIGENCIADO';
        $estado = (new EstadoDocumental())->findByCode($estadoCodigo);

        if (!$estado) {
            Session::flash('error', 'No existe el estado documental requerido.');
            redirect('/formatos/create?tipo_formato_id=' . $tipoId);
            exit;
        }

        $registroModel = new FormatoRegistro();
        $consecutivo = $registroModel->nextConsecutivo($granja['sigla'], $tipo['codigo']);
        $datosJson = json_encode($datos, JSON_UNESCAPED_UNICODE);
        $hash = hash('sha256', $consecutivo . '|' . $datosJson . '|' . (Auth::id() ?? 0) . '|' . date('c'));

        $payload = [
            'tipo_formato_id' => $tipoId,
            'granja_id' => $granjaId,
            'galpon_id' => ((int) ($_POST['galpon_id'] ?? 0)) ?: null,
            'ciclo_id' => ((int) ($_POST['ciclo_id'] ?? 0)) ?: null,
            'usuario_id' => Auth::id(),
            'estado_documental_id' => (int) $estado['id'],
            'codigo_consecutivo' => $consecutivo,
            'fecha_formato' => $_POST['fecha_formato'] ?? date('Y-m-d'),
            'hora_formato' => ($_POST['hora_formato'] ?? '') ?: date('H:i:s'),
            'es_no_aplica' => $esNoAplica,
            'motivo_no_aplica' => $esNoAplica ? $motivoNoAplica : null,
            'datos_json' => $datosJson,
            'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
            'hash_documento' => $hash,
            'version' => 1,
            'origen_registro' => 'web',
            'sincronizado' => 1,
        ];

        $id = $registroModel->create($payload);
        (new Auditoria())->record(Auth::id(), 'formatos', 'crear', 'formatos_registros', $id, null, $payload);

        $incidenciaModel = new IncidenciaCalidad();
        foreach (($validation['warnings'] ?? []) as $warning) {
            $incidenciaModel->create($id, 'validacion', 'general', $warning, 'media');
        }
        foreach ($spellingWarnings as $warning) {
            $incidenciaModel->create($id, 'ortografia', 'observaciones', $warning, 'baja');
        }


        Session::flash('success', 'Formato diligenciado correctamente.');
        redirect('/formatos/show?id=' . $id);
        exit;
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $registro = (new FormatoRegistro())->find($id);

        if (!$registro) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Detalle de formato';
        $firmas = (new \App\Models\FirmaDigital())->byRegistro($id);
        $incidencias = (new \App\Models\IncidenciaCalidad())->byRegistro($id);
        require dirname(__DIR__) . '/Views/formatos/show.php';
    }
}
