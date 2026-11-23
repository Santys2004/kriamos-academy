<?php

use App\Controllers\AuthController;
use App\Controllers\CicloController;
use App\Controllers\DashboardController;
use App\Controllers\DatabaseController;
use App\Controllers\GalponController;
use App\Controllers\FormatoController;
use App\Controllers\AuditoriaIcaController;
use App\Controllers\FirmaAvanzadaController;
use App\Controllers\BiExternalController;
use App\Controllers\Api\BiExternalApiController;
use App\Controllers\MobileController;
use App\Controllers\Api\MobileApiController;
use App\Controllers\AI\AiController;
use App\Controllers\ApiAdminController;
use App\Controllers\Api\ApiPublicController;
use App\Controllers\BiController;
use App\Controllers\NotificacionController;
use App\Controllers\AdministracionController;
use App\Controllers\UsuarioAdminController;
use App\Controllers\RolController;
use App\Controllers\ParametroController;
use App\Controllers\AuditoriaController;
use App\Controllers\SeguridadController;
use App\Controllers\DespliegueController;
use App\Controllers\AyudaController;
use App\Controllers\SincronizacionController;
use App\Controllers\ApiSyncController;
use App\Controllers\DocumentoController;
use App\Controllers\ReporteController;
use App\Controllers\AlertaController;
use App\Controllers\FirmaController;
use App\Controllers\CalidadController;
use App\Controllers\GranjaController;

return [
    'GET /' => [DashboardController::class, 'index'],

    'GET /login' => [AuthController::class, 'login'],
    'POST /login' => [AuthController::class, 'authenticate'],
    'POST /logout' => [AuthController::class, 'logout'],

    'GET /database/schema' => [DatabaseController::class, 'schema'],

    'GET /granjas' => [GranjaController::class, 'index'],
    'GET /granjas/show' => [GranjaController::class, 'show'],
    'GET /granjas/create' => [GranjaController::class, 'create'],
    'POST /granjas/store' => [GranjaController::class, 'store'],
    'GET /granjas/edit' => [GranjaController::class, 'edit'],
    'POST /granjas/update' => [GranjaController::class, 'update'],

    'GET /galpones' => [GalponController::class, 'index'],
    'GET /galpones/create' => [GalponController::class, 'create'],
    'POST /galpones/store' => [GalponController::class, 'store'],
    'GET /galpones/edit' => [GalponController::class, 'edit'],
    'POST /galpones/update' => [GalponController::class, 'update'],

    'GET /ciclos' => [CicloController::class, 'index'],
    'GET /ciclos/create' => [CicloController::class, 'create'],
    'POST /ciclos/store' => [CicloController::class, 'store'],
    'GET /ciclos/edit' => [CicloController::class, 'edit'],
    'POST /ciclos/update' => [CicloController::class, 'update'],

    'GET /formatos' => [FormatoController::class, 'index'],
    'GET /formatos/create' => [FormatoController::class, 'create'],
    'POST /formatos/store' => [FormatoController::class, 'store'],
    'GET /formatos/show' => [FormatoController::class, 'show'],

    'GET /firmas/create' => [FirmaController::class, 'create'],
    'POST /firmas/store' => [FirmaController::class, 'store'],

    'GET /calidad' => [CalidadController::class, 'index'],

    'GET /alertas' => [AlertaController::class, 'index'],
    'GET /alertas/show' => [AlertaController::class, 'show'],
    'GET /alertas/generar' => [AlertaController::class, 'generar'],
    'POST /alertas/estado' => [AlertaController::class, 'estado'],
    'GET /alertas/enviar-correos' => [AlertaController::class, 'enviarCorreos'],

    'GET /reportes' => [ReporteController::class, 'index'],
    'GET /reportes/formatos' => [ReporteController::class, 'formatos'],
    'GET /reportes/formatos/csv' => [ReporteController::class, 'formatosCsv'],
    'GET /reportes/alertas' => [ReporteController::class, 'alertas'],
    'GET /reportes/alertas/csv' => [ReporteController::class, 'alertasCsv'],
    'GET /reportes/certificaciones' => [ReporteController::class, 'certificaciones'],
    'GET /reportes/incidencias' => [ReporteController::class, 'incidencias'],
    'GET /reportes/paquete-ica' => [ReporteController::class, 'paqueteIca'],

    'GET /documentos' => [DocumentoController::class, 'index'],
    'GET /documentos/create' => [DocumentoController::class, 'create'],
    'POST /documentos/store' => [DocumentoController::class, 'store'],
    'GET /documentos/show' => [DocumentoController::class, 'show'],
    'GET /documentos/edit' => [DocumentoController::class, 'edit'],
    'POST /documentos/update' => [DocumentoController::class, 'update'],
    'GET /documentos/download' => [DocumentoController::class, 'download'],
    'POST /evidencias/store' => [DocumentoController::class, 'evidenciaStore'],

    'GET /sincronizacion' => [SincronizacionController::class, 'index'],
    'POST /api/sync/formato' => [ApiSyncController::class, 'formato'],

    'GET /ayuda' => [AyudaController::class, 'index'],
    'GET /ayuda/articulo' => [AyudaController::class, 'show'],
    'GET /ayuda/manual' => [AyudaController::class, 'manual'],
    'GET /ayuda/chat' => [AyudaController::class, 'chat'],
    'POST /ayuda/chat/ask' => [AyudaController::class, 'chatAsk'],
    'POST /ayuda/chat/rate' => [AyudaController::class, 'chatRate'],

    'GET /seguridad' => [SeguridadController::class, 'index'],
    'GET /seguridad/health' => [SeguridadController::class, 'health'],
    'GET /despliegue/checklist' => [DespliegueController::class, 'checklist'],

    'GET /admin' => [AdministracionController::class, 'index'],

    'GET /admin/usuarios' => [UsuarioAdminController::class, 'index'],
    'GET /admin/usuarios/create' => [UsuarioAdminController::class, 'create'],
    'POST /admin/usuarios/store' => [UsuarioAdminController::class, 'store'],
    'GET /admin/usuarios/edit' => [UsuarioAdminController::class, 'edit'],
    'POST /admin/usuarios/update' => [UsuarioAdminController::class, 'update'],
    'POST /admin/usuarios/reset-password' => [UsuarioAdminController::class, 'resetPassword'],
    'POST /admin/usuarios/estado' => [UsuarioAdminController::class, 'estado'],

    'GET /admin/roles' => [RolController::class, 'index'],
    'GET /admin/roles/edit' => [RolController::class, 'edit'],
    'POST /admin/roles/update' => [RolController::class, 'update'],

    'GET /admin/parametros' => [ParametroController::class, 'index'],
    'POST /admin/parametros/update' => [ParametroController::class, 'update'],

    'GET /admin/auditoria' => [AuditoriaController::class, 'index'],
    'GET /admin/auditoria/csv' => [AuditoriaController::class, 'csv'],

    'GET /notificaciones' => [NotificacionController::class, 'index'],
    'GET /notificaciones/show' => [NotificacionController::class, 'show'],
    'GET /notificaciones/correos' => [NotificacionController::class, 'mailQueue'],
    'GET /notificaciones/procesar-cola' => [NotificacionController::class, 'procesarCola'],
    'GET /notificaciones/generar-desde-alertas' => [NotificacionController::class, 'generarDesdeAlertas'],
    'GET /notificaciones/push' => [NotificacionController::class, 'pushPanel'],
    'POST /api/push/subscribe' => [NotificacionController::class, 'savePushSubscription'],

    'GET /bi' => [BiController::class, 'index'],
    'GET /bi/indicadores' => [BiController::class, 'indicadores'],
    'GET /bi/export-csv' => [BiController::class, 'exportCsv'],
    'GET /bi/snapshot' => [BiController::class, 'snapshot'],
    'GET /bi/snapshots' => [BiController::class, 'snapshots'],

    'GET /api-admin' => [ApiAdminController::class, 'index'],
    'GET /api-admin/clientes/create' => [ApiAdminController::class, 'create'],
    'POST /api-admin/clientes/store' => [ApiAdminController::class, 'store'],
    'GET /api-admin/clientes/show' => [ApiAdminController::class, 'show'],
    'POST /api-admin/tokens/generate' => [ApiAdminController::class, 'generateToken'],
    'POST /api-admin/tokens/revoke' => [ApiAdminController::class, 'revokeToken'],

    'GET /api/v1/health' => [ApiPublicController::class, 'health'],
    'GET /api/v1/granjas' => [ApiPublicController::class, 'granjas'],
    'GET /api/v1/galpones' => [ApiPublicController::class, 'galpones'],
    'GET /api/v1/formatos' => [ApiPublicController::class, 'formatos'],
    'GET /api/v1/alertas' => [ApiPublicController::class, 'alertas'],
    'GET /api/v1/documentos' => [ApiPublicController::class, 'documentos'],
    'GET /api/v1/bi' => [ApiPublicController::class, 'bi'],

    'GET /ia' => [AiController::class, 'index'],
    'POST /ia/ask' => [AiController::class, 'ask'],
    'GET /ia/contextos' => [AiController::class, 'contextos'],
    'GET /ia/contextos/show' => [AiController::class, 'contextoShow'],
    'GET /ia/contextos/indexar' => [AiController::class, 'indexar'],
    'GET /ia/consultas' => [AiController::class, 'consultas'],
    'GET /ia/consultas/csv' => [AiController::class, 'consultasCsv'],

    'GET /mobile' => [MobileController::class, 'index'],

    'POST /api/mobile/v1/login' => [MobileApiController::class, 'login'],
    'GET /api/mobile/v1/me' => [MobileApiController::class, 'me'],
    'POST /api/mobile/v1/register-device' => [MobileApiController::class, 'registerDevice'],

    'GET /bi-externo' => [BiExternalController::class, 'index'],
    'GET /bi-externo/conexiones/create' => [BiExternalController::class, 'createConnection'],
    'POST /bi-externo/conexiones/store' => [BiExternalController::class, 'storeConnection'],
    'GET /bi-externo/datasets' => [BiExternalController::class, 'datasets'],

    'GET /api/bi/v1/catalog' => [BiExternalApiController::class, 'catalog'],
    'GET /api/bi/v1/dataset' => [BiExternalApiController::class, 'dataset'],

    'GET /firma-avanzada' => [FirmaAvanzadaController::class, 'index'],
    'GET /firma-avanzada/csv' => [FirmaAvanzadaController::class, 'csv'],
    'GET /firma-avanzada/certificados' => [FirmaAvanzadaController::class, 'certificados'],
    'POST /firma-avanzada/certificados/store' => [FirmaAvanzadaController::class, 'crearCertificado'],
    'GET /firma-avanzada/solicitar-otp' => [FirmaAvanzadaController::class, 'solicitarOtp'],
    'POST /firma-avanzada/firmar-formato' => [FirmaAvanzadaController::class, 'firmarFormato'],
    'POST /firma-avanzada/firmar-documento' => [FirmaAvanzadaController::class, 'firmarDocumento'],
    'GET /firma-avanzada/verificar' => [FirmaAvanzadaController::class, 'verificar'],

    'GET /auditoria-ica' => [AuditoriaIcaController::class, 'index'],
    'GET /auditoria-ica/create' => [AuditoriaIcaController::class, 'create'],
    'POST /auditoria-ica/store' => [AuditoriaIcaController::class, 'store'],
    'GET /auditoria-ica/show' => [AuditoriaIcaController::class, 'show'],
    'POST /auditoria-ica/matriz/update' => [AuditoriaIcaController::class, 'updateMatriz'],
    'POST /auditoria-ica/hallazgos/store' => [AuditoriaIcaController::class, 'storeHallazgo'],
    'POST /auditoria-ica/planes/store' => [AuditoriaIcaController::class, 'storePlan'],
    'GET /auditoria-ica/generar-paquete' => [AuditoriaIcaController::class, 'generarPaquete'],
    'GET /auditoria-ica/csv' => [AuditoriaIcaController::class, 'csv'],
];
