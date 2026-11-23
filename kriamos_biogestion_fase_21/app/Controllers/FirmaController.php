<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\EstadoDocumental;
use App\Models\FirmaDigital;
use App\Models\FormatoRegistro;
use App\Models\User;
use App\Services\Auth;
use App\Services\Csrf;
use App\Services\Session;

class FirmaController
{
    public function create(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.aprobar');

        $id = (int) ($_GET['id'] ?? 0);
        $registro = (new FormatoRegistro())->find($id);

        if (!$registro) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $pageTitle = 'Firmar formato';
        require dirname(__DIR__) . '/Views/firmas/create.php';
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('formatos.aprobar');

        if (!Csrf::validate($_POST['_csrf_token'] ?? null)) {
            Session::flash('error', 'Formulario inválido. Intente nuevamente.');
            redirect('/formatos');
            exit;
        }

        $registroId = (int) ($_POST['formato_registro_id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $firmaBase64 = $_POST['firma_base64'] ?? '';

        $registroModel = new FormatoRegistro();
        $registro = $registroModel->find($registroId);

        if (!$registro) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if ((new FirmaDigital())->existsForRegistro($registroId)) {
            Session::flash('error', 'Este formato ya tiene una firma registrada.');
            redirect('/formatos/show?id=' . $registroId);
            exit;
        }

        $user = (new User())->findWithRoleAndPermissions(Auth::id());

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'La contraseña de confirmación no es válida.');
            redirect('/firmas/create?id=' . $registroId);
            exit;
        }

        $firmaPath = null;

        if (str_starts_with($firmaBase64, 'data:image/png;base64,')) {
            $binary = base64_decode(str_replace('data:image/png;base64,', '', $firmaBase64));
            if ($binary !== false) {
                $dir = dirname(__DIR__, 2) . '/public/uploads/firmas';
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $filename = 'firma_' . $registroId . '_' . time() . '.png';
                file_put_contents($dir . '/' . $filename, $binary);
                $firmaPath = '/uploads/firmas/' . $filename;
            }
        }

        $hash = hash('sha256', $registro['codigo_consecutivo'] . '|' . Auth::id() . '|' . date('c') . '|' . ($firmaPath ?? ''));

        (new FirmaDigital())->create([
            'formato_registro_id' => $registroId,
            'usuario_id' => Auth::id(),
            'firma_path' => $firmaPath,
            'metodo' => $firmaPath ? 'dibujo' : 'password',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'hash_firma' => $hash,
        ]);

        $estado = (new EstadoDocumental())->findByCode('APROBADO');
        if ($estado) {
            $registroModel->aprobarConFirma($registroId, (int) $estado['id']);
        }

        (new Auditoria())->record(Auth::id(), 'firmas', 'firmar', 'firmas_digitales', $registroId, null, [
            'formato_registro_id' => $registroId,
            'hash_firma' => $hash,
            'metodo' => $firmaPath ? 'dibujo' : 'password',
        ]);

        Session::flash('success', 'Formato firmado y aprobado correctamente.');
        redirect('/formatos/show?id=' . $registroId);
        exit;
    }
}
