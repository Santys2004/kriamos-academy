<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\CertificadoFirma;
use App\Models\FirmaAvanzada;
use App\Models\UsuarioAdmin;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\ExportService;
use App\Services\Session;
use App\Services\Signature\AdvancedSignatureService;
use App\Services\Signature\OtpSignatureService;
use App\Services\Signature\SignatureVerificationService;

class FirmaAvanzadaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.ver');

        $pageTitle = 'Firma electrónica avanzada';
        $filters = [
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ];

        $model = new FirmaAvanzada();
        $firmas = $model->all($filters);
        $usuarios = (new UsuarioAdmin())->all();

        $metricas = [
            'firmadas' => $model->countByEstado('firmada'),
            'selladas' => $model->countByEstado('sellada'),
            'revocadas' => $model->countByEstado('revocada'),
        ];

        require dirname(__DIR__) . '/Views/firma_avanzada/index.php';
    }

    public function certificados(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.certificados');

        $pageTitle = 'Certificados de firma';
        $certificados = (new CertificadoFirma())->all();
        $usuarios = (new UsuarioAdmin())->all();

        require dirname(__DIR__) . '/Views/firma_avanzada/certificados/index.php';
    }

    public function crearCertificado(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.gestionar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/firma-avanzada/certificados');
            exit;
        }

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $serial = 'KRIAMOS-CERT-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $huella = hash('sha256', $serial . '|' . $usuarioId . '|' . microtime(true));

        $id = (new CertificadoFirma())->create([
            'usuario_id' => $usuarioId,
            'tipo' => $_POST['tipo'] ?? 'interno',
            'serial' => $serial,
            'huella_sha256' => $huella,
            'entidad_emisora' => $_POST['entidad_emisora'] ?? 'Kriamos Autoridad Interna',
            'vigente_desde' => $_POST['vigente_desde'] ?? date('Y-m-d'),
            'vigente_hasta' => $_POST['vigente_hasta'] ?? date('Y-m-d', strtotime('+1 year')),
            'estado' => 'activo',
            'metadata_json' => json_encode([
                'created_by' => Auth::id(),
                'source' => 'fase_20_internal_certificate',
            ], JSON_UNESCAPED_UNICODE),
        ]);

        (new Auditoria())->record(Auth::id(), 'firma_avanzada', 'crear_certificado', 'certificados_firma', $id, null, ['usuario_id' => $usuarioId]);

        Session::flash('success', 'Certificado interno creado correctamente.');
        redirect('/firma-avanzada/certificados');
        exit;
    }

    public function solicitarOtp(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.firmar');

        $code = (new OtpSignatureService())->generate(Auth::id());

        Session::flash('success', 'OTP generado para demostración interna: ' . $code . '. En producción debe enviarse por canal seguro.');
        redirect($_SERVER['HTTP_REFERER'] ?? '/firma-avanzada');
        exit;
    }

    public function firmarFormato(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.firmar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/firma-avanzada');
            exit;
        }

        $formatoId = (int) ($_POST['formato_registro_id'] ?? 0);
        $otp = trim($_POST['otp'] ?? '');

        $result = (new AdvancedSignatureService())->signFormato($formatoId, $otp);

        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            redirect('/firma-avanzada');
            exit;
        }

        (new Auditoria())->record(Auth::id(), 'firma_avanzada', 'firmar_formato', 'firmas_avanzadas', $result['firma_id'], null, $result);

        Session::flash('success', $result['message']);
        redirect('/firma-avanzada');
        exit;
    }

    public function firmarDocumento(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.firmar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido.');
            redirect('/firma-avanzada');
            exit;
        }

        $documentoId = (int) ($_POST['documento_id'] ?? 0);
        $otp = trim($_POST['otp'] ?? '');

        $result = (new AdvancedSignatureService())->signDocumento($documentoId, $otp);

        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            redirect('/firma-avanzada');
            exit;
        }

        (new Auditoria())->record(Auth::id(), 'firma_avanzada', 'firmar_documento', 'firmas_avanzadas', $result['firma_id'], null, $result);

        Session::flash('success', $result['message']);
        redirect('/firma-avanzada');
        exit;
    }

    public function verificar(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.verificar');

        $id = (int) ($_GET['id'] ?? 0);
        $result = (new SignatureVerificationService())->verify($id);

        Session::flash($result['ok'] ? 'success' : 'error', $result['message']);
        redirect('/firma-avanzada');
        exit;
    }

    public function csv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('firma_avanzada.ver');

        $rows = (new FirmaAvanzada())->all([
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ]);

        ExportService::csv('firmas_avanzadas_kriamos_' . date('Ymd_His') . '.csv', [
            'created_at' => 'Fecha',
            'email' => 'Usuario',
            'tipo_objeto' => 'Tipo objeto',
            'objeto_id' => 'Objeto ID',
            'hash_original' => 'Hash original',
            'hash_firma' => 'Hash firma',
            'estado' => 'Estado',
            'estado_verificacion' => 'Estado verificación',
            'mensaje_verificacion' => 'Mensaje verificación',
        ], $rows);
    }
}
