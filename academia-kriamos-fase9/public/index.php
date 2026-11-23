<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/controllers/PersonController.php';
require_once __DIR__ . '/../app/controllers/CourseController.php';
require_once __DIR__ . '/../app/controllers/ExamController.php';
require_once __DIR__ . '/../app/controllers/CertificateController.php';
require_once __DIR__ . '/../app/controllers/CalendarController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/RewardController.php';
require_once __DIR__ . '/../app/controllers/OfflineController.php';
require_once __DIR__ . '/../app/controllers/ChatAIController.php';

$app = app_config();
date_default_timezone_set($app['timezone']);

$page = $_GET['page'] ?? 'home';
$personController = new PersonController();
$courseController = new CourseController();
$examController = new ExamController();
$certificateController = new CertificateController();
$calendarController = new CalendarController();
$dashboardController = new DashboardController();
$rewardController = new RewardController();
$offlineController = new OfflineController();
$chatAIController = new ChatAIController();

try {
    match ($page) {
        'home' => view('home', ['title' => 'Inicio']),
        'registro' => view('registro', [
            'title' => 'Registro de capacitación',
            'tipo' => $_GET['tipo'] ?? 'empleado',
            'old' => ['person_type' => $_GET['tipo'] ?? 'empleado'],
            'errors' => [],
        ]),
        'guardar_registro' => is_post()
            ? $personController->store()
            : redirect(current_url('registro')),
        'admin_personas' => $personController->list(),
        'verificar' => view('verificar', ['title' => 'Verificar registro']),
        'verificar_resultado' => is_post()
            ? $personController->verify()
            : redirect(current_url('verificar')),

        // Fase 2: administración de cursos, módulos, materiales y asignaciones.
        'admin_cursos' => $courseController->list(),
        'crear_curso' => $courseController->create(),
        'guardar_curso' => is_post()
            ? $courseController->store()
            : redirect(current_url('crear_curso')),
        'curso_detalle' => $courseController->show(),
        'crear_modulo' => $courseController->createModule(),
        'guardar_modulo' => is_post()
            ? $courseController->storeModule()
            : redirect(current_url('admin_cursos')),
        'subir_material' => $courseController->createMaterial(),
        'guardar_material' => is_post()
            ? $courseController->storeMaterial()
            : redirect(current_url('admin_cursos')),
        'crear_asignacion' => $courseController->createAssignment(),
        'guardar_asignacion' => is_post()
            ? $courseController->storeAssignment()
            : redirect(current_url('admin_cursos')),

        // Fase 3: banco de preguntas y motor de examen.
        'preguntas_modulo' => $examController->questionBank(),
        'crear_pregunta' => $examController->createQuestion(),
        'guardar_pregunta' => is_post()
            ? $examController->storeQuestion()
            : redirect(current_url('admin_cursos')),
        'mis_capacitaciones' => $examController->myTrainings(),
        'mis_capacitaciones_resultado' => is_post()
            ? $examController->myTrainingsResult()
            : redirect(current_url('mis_capacitaciones')),
        'iniciar_examen' => is_post()
            ? $examController->startExam()
            : redirect(current_url('mis_capacitaciones')),
        'presentar_examen' => $examController->takeExam(),
        'guardar_respuesta' => is_post()
            ? $examController->submitAnswer()
            : redirect(current_url('mis_capacitaciones')),
        'resultado_examen' => $examController->result(),


        // Fase 4: certificados, PDF, QR, vigencia e historial.
        'certificados' => $certificateController->adminList(),
        'historial_certificados' => $certificateController->historyForm(),
        'historial_certificados_resultado' => is_post()
            ? $certificateController->historyResult()
            : redirect(current_url('historial_certificados')),
        'ver_certificado' => $certificateController->show(),
        'descargar_certificado' => $certificateController->download(),

        // Fase 5: calendario de recertificación y alertas por correo.
        'admin_calendario' => $calendarController->adminList(),
        'procesar_recordatorios' => is_post()
            ? $calendarController->processReminders()
            : redirect(current_url('admin_calendario')),
        'enviar_recordatorio_manual' => is_post()
            ? $calendarController->sendManualReminder()
            : redirect(current_url('admin_calendario')),
        'mi_calendario' => $calendarController->personForm(),
        'mi_calendario_resultado' => is_post()
            ? $calendarController->personResult()
            : redirect(current_url('mi_calendario')),

        // Fase 6: dashboard gerencial, KPIs, completitud y habilidades.
        'dashboard' => $dashboardController->index(),
        'dashboard_export' => $dashboardController->export(),

        // Fase 7: recompensas para empleados por resultados destacados.
        'admin_recompensas' => $rewardController->adminList(),
        'marcar_recompensa_entregada' => is_post()
            ? $rewardController->markDelivered()
            : redirect(current_url('admin_recompensas')),
        'anular_recompensa' => is_post()
            ? $rewardController->voidReward()
            : redirect(current_url('admin_recompensas')),
        'mis_recompensas' => $rewardController->personForm(),
        'mis_recompensas_resultado' => is_post()
            ? $rewardController->personResult()
            : redirect(current_url('mis_recompensas')),
        'recompensas_export' => $rewardController->export(),


        // Fase 9: PWA y modo sin conexión para estudiar materiales descargados.
        'offline' => $offlineController->form(),
        'offline_resultado' => is_post()
            ? $offlineController->result()
            : redirect(current_url('offline')),
        'registrar_descarga_offline' => is_post()
            ? $offlineController->registerDownload()
            : redirect(current_url('offline')),

        // Fase 9: Chat IA con base de conocimiento interna.
        'chat_ia' => $chatAIController->form(),
        'chat_ia_iniciar' => is_post()
            ? $chatAIController->start()
            : redirect(current_url('chat_ia')),
        'chat_ia_preguntar' => is_post()
            ? $chatAIController->ask()
            : redirect(current_url('chat_ia')),
        'admin_chat_ia' => $chatAIController->admin(),
        'guardar_conocimiento_ia' => is_post()
            ? $chatAIController->storeKnowledge()
            : redirect(current_url('admin_chat_ia')),
        'cambiar_estado_conocimiento_ia' => is_post()
            ? $chatAIController->toggleKnowledge()
            : redirect(current_url('admin_chat_ia')),

        default => view('home', ['title' => 'Inicio']),
    };
} catch (Throwable $e) {
    http_response_code(500);
    view('home', ['title' => 'Error', 'error' => $e->getMessage()]);
}
