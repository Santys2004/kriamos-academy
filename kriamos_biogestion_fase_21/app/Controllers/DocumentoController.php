<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\CategoriaDocumental;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Evidencia;
use App\Models\Galpon;
use App\Models\Granja;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\FileUploadService;
use App\Services\Session;

class DocumentoController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.ver');

        $pageTitle = 'Núcleo documental';
        $filters = [
            'q' => $_GET['q'] ?? '',
            'categoria_documental_id' => $_GET['categoria_documental_id'] ?? '',
            'granja_id' => $_GET['granja_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
        ];

        $documentoModel = new Documento();
        $documentos = $documentoModel->search($filters);
        $categorias = (new CategoriaDocumental())->all();
        $granjas = (new Granja())->active();

        $metricas = [
            'total' => $documentoModel->count(),
            'vigentes' => $documentoModel->countByEstado('vigente'),
            'revision' => $documentoModel->countByEstado('en_revision'),
            'obsoletos' => $documentoModel->countByEstado('obsoleto'),
        ];

        require dirname(__DIR__) . '/Views/documentos/index.php';
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.crear');

        $pageTitle = 'Cargar documento';
        $documento = null;
        $categorias = (new CategoriaDocumental())->all();
        $granjas = (new Granja())->active();
        $galpones = (new Galpon())->all();
        $action = '/documentos/store';

        require dirname(__DIR__) . '/Views/documentos/form.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/documentos/create');
            exit;
        }

        $upload = (new FileUploadService())->store($_FILES['archivo'] ?? [], 'documento');
        if (!$upload['ok']) {
            Session::flash('error', $upload['message']);
            redirect('/documentos/create');
            exit;
        }

        $data = [
            'categoria_documental_id' => (int) ($_POST['categoria_documental_id'] ?? 0),
            'granja_id' => ((int) ($_POST['granja_id'] ?? 0)) ?: null,
            'galpon_id' => ((int) ($_POST['galpon_id'] ?? 0)) ?: null,
            'usuario_id' => Auth::id(),
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'archivo_path' => $upload['archivo_path'],
            'mime_type' => $upload['mime_type'],
            'extension' => $upload['extension'],
            'peso_bytes' => $upload['peso_bytes'],
            'version' => 1,
            'estado' => $_POST['estado'] ?? 'vigente',
        ];

        if ($data['categoria_documental_id'] <= 0 || $data['titulo'] === '') {
            Session::flash('error', 'Debe diligenciar categoría y título.');
            redirect('/documentos/create');
            exit;
        }

        $id = (new Documento())->create($data);

        (new DocumentoVersion())->create([
            'documento_id' => $id,
            'usuario_id' => Auth::id(),
            'version' => 1,
            'archivo_path' => $upload['archivo_path'],
            'mime_type' => $upload['mime_type'],
            'extension' => $upload['extension'],
            'peso_bytes' => $upload['peso_bytes'],
            'comentario' => 'Versión inicial del documento.',
        ]);

        (new Auditoria())->record(Auth::id(), 'documentos', 'crear', 'documentos', $id, null, $data);

        Session::flash('success', 'Documento cargado correctamente.');
        redirect('/documentos/show?id=' . $id);
        exit;
    }

    public function show(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $documento = (new Documento())->find($id);

        if (!$documento) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Detalle documental';
        $versiones = (new DocumentoVersion())->byDocumento($id);
        $evidencias = (new Evidencia())->byDocumento($id);

        require dirname(__DIR__) . '/Views/documentos/show.php';
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.editar');

        $id = (int) ($_GET['id'] ?? 0);
        $documento = (new Documento())->find($id);

        if (!$documento) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Editar documento';
        $categorias = (new CategoriaDocumental())->all();
        $granjas = (new Granja())->active();
        $galpones = (new Galpon())->all();
        $action = '/documentos/update?id=' . $id;

        require dirname(__DIR__) . '/Views/documentos/form.php';
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.editar');

        $id = (int) ($_GET['id'] ?? 0);
        $model = new Documento();
        $before = $model->find($id);

        if (!$before) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/documentos/edit?id=' . $id);
            exit;
        }

        $data = [
            'categoria_documental_id' => (int) ($_POST['categoria_documental_id'] ?? 0),
            'granja_id' => ((int) ($_POST['granja_id'] ?? 0)) ?: null,
            'galpon_id' => ((int) ($_POST['galpon_id'] ?? 0)) ?: null,
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '') ?: null,
            'estado' => $_POST['estado'] ?? 'vigente',
        ];

        $model->updateMetadata($id, $data);

        if (!empty($_FILES['archivo']['name'])) {
            $upload = (new FileUploadService())->store($_FILES['archivo'], 'documento_v' . ((int) $before['version'] + 1));
            if (!$upload['ok']) {
                Session::flash('error', $upload['message']);
                redirect('/documentos/edit?id=' . $id);
                exit;
            }

            $model->updateFile($id, [
                'archivo_path' => $upload['archivo_path'],
                'mime_type' => $upload['mime_type'],
                'extension' => $upload['extension'],
                'peso_bytes' => $upload['peso_bytes'],
                'estado' => $data['estado'],
            ]);

            (new DocumentoVersion())->create([
                'documento_id' => $id,
                'usuario_id' => Auth::id(),
                'version' => ((int) $before['version']) + 1,
                'archivo_path' => $upload['archivo_path'],
                'mime_type' => $upload['mime_type'],
                'extension' => $upload['extension'],
                'peso_bytes' => $upload['peso_bytes'],
                'comentario' => trim($_POST['comentario_version'] ?? '') ?: 'Nueva versión cargada.',
            ]);
        }

        (new Auditoria())->record(Auth::id(), 'documentos', 'editar', 'documentos', $id, $before, $data);

        Session::flash('success', 'Documento actualizado correctamente.');
        redirect('/documentos/show?id=' . $id);
        exit;
    }

    public function evidenciaStore(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.crear');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/documentos');
            exit;
        }

        $documentoId = ((int) ($_POST['documento_id'] ?? 0)) ?: null;
        $formatoId = ((int) ($_POST['formato_registro_id'] ?? 0)) ?: null;

        $service = new FileUploadService();
        $upload = $service->store($_FILES['archivo'] ?? [], 'evidencia');

        if (!$upload['ok']) {
            Session::flash('error', $upload['message']);
            redirect($documentoId ? '/documentos/show?id=' . $documentoId : '/documentos');
            exit;
        }

        (new Evidencia())->create([
            'formato_registro_id' => $formatoId,
            'documento_id' => $documentoId,
            'usuario_id' => Auth::id(),
            'tipo' => $service->extensionToTipo($upload['extension']),
            'titulo' => trim($_POST['titulo'] ?? '') ?: $upload['original_name'],
            'archivo_path' => $upload['archivo_path'],
            'mime_type' => $upload['mime_type'],
            'extension' => $upload['extension'],
            'peso_bytes' => $upload['peso_bytes'],
            'latitud' => ($_POST['latitud'] ?? '') !== '' ? (float) $_POST['latitud'] : null,
            'longitud' => ($_POST['longitud'] ?? '') !== '' ? (float) $_POST['longitud'] : null,
            'tomada_at' => ($_POST['tomada_at'] ?? '') ?: null,
        ]);

        Session::flash('success', 'Evidencia cargada correctamente.');
        redirect($documentoId ? '/documentos/show?id=' . $documentoId : '/documentos');
        exit;
    }

    public function download(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('documentos.ver');

        $id = (int) ($_GET['id'] ?? 0);
        $documento = (new Documento())->find($id);

        if (!$documento) {
            http_response_code(404);
            exit('Documento no encontrado.');
        }

        $path = dirname(__DIR__, 2) . '/public' . $documento['archivo_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            exit('Archivo no encontrado.');
        }

        header('Content-Type: ' . ($documento['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
