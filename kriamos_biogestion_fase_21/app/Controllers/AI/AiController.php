<?php

namespace App\Controllers\AI;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\AiConsulta;
use App\Models\AiContexto;
use App\Models\Auditoria;
use App\Models\UsuarioAdmin;
use App\Services\AI\AIAssistantService;
use App\Services\AI\ContextIndexerService;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\ExportService;
use App\Services\Session;

class AiController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.ver');

        $pageTitle = 'IA Documental';
        $contextModel = new AiContexto();
        $consultaModel = new AiConsulta();

        $metricas = [
            'contextos' => $contextModel->count(),
            'documentos' => $contextModel->countByTipo('documento'),
            'consultas_hoy' => $consultaModel->countToday(),
            'consultas_locales' => $consultaModel->countByModo('local'),
        ];

        require dirname(__DIR__, 2) . '/Views/ia/index.php';
    }

    public function ask(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.consultar');

        header('Content-Type: application/json; charset=utf-8');

        $question = trim($_POST['pregunta'] ?? '');
        $mode = $_POST['modo'] ?? 'local';

        if ($question === '') {
            echo json_encode(['ok' => false, 'message' => 'Debe escribir una pregunta.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = (new AIAssistantService())->ask($question, $mode);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function contextos(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.contextos');

        $pageTitle = 'Contextos IA';
        $filters = [
            'q' => $_GET['q'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'estado' => $_GET['estado'] ?? '',
        ];

        $contextos = (new AiContexto())->all($filters);

        require dirname(__DIR__, 2) . '/Views/ia/contextos/index.php';
    }

    public function contextoShow(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.contextos');

        $id = (int) ($_GET['id'] ?? 0);
        $contexto = (new AiContexto())->find($id);

        if (!$contexto) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Detalle contexto IA';
        require dirname(__DIR__, 2) . '/Views/ia/contextos/show.php';
    }

    public function indexar(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.gestionar');

        $result = (new ContextIndexerService())->indexAll(Auth::id());

        (new Auditoria())->record(Auth::id(), 'ia', 'indexar_contexto', 'ai_contextos', null, null, $result);

        Session::flash('success', 'Contexto IA indexado. Ayuda: ' . $result['ayuda'] . ', documentos: ' . $result['documentos'] . ', tipos de formato: ' . $result['tipos_formato']);
        redirect('/ia/contextos');
        exit;
    }

    public function consultas(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.ver_logs');

        $pageTitle = 'Consultas IA';
        $filters = [
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ];

        $consultas = (new AiConsulta())->recientes($filters);
        $usuarios = (new UsuarioAdmin())->all();

        require dirname(__DIR__, 2) . '/Views/ia/consultas/index.php';
    }

    public function consultasCsv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('ia.ver_logs');

        $rows = (new AiConsulta())->recientes([
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ]);

        ExportService::csv('consultas_ia_kriamos_' . date('Ymd_His') . '.csv', [
            'created_at' => 'Fecha',
            'email' => 'Usuario',
            'modo' => 'Modo',
            'proveedor' => 'Proveedor',
            'pregunta' => 'Pregunta',
            'respuesta' => 'Respuesta',
            'tokens_estimados' => 'Tokens estimados',
        ], $rows);
    }
}
