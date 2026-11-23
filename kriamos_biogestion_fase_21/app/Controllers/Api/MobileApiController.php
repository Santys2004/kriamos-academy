<?php

namespace App\Controllers\Api;

use App\Models\MobileDevice;
use App\Models\Usuario;
use App\Services\Api\ApiResponse;
use App\Services\Database;

class MobileApiController
{
    public function login(): void
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $email = strtolower(trim($body['email'] ?? ''));
        $password = $body['password'] ?? '';

        if ($email === '' || $password === '') {
            ApiResponse::error('Correo y contraseña son obligatorios.', 422);
        }

        $stmt = Database::connection()->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.email = :email
              AND u.estado = 'activo'
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            ApiResponse::error('Credenciales inválidas.', 401);
        }

        $plainToken = 'mobile_' . bin2hex(random_bytes(32));

        $insert = Database::connection()->prepare("
            INSERT INTO mobile_tokens (
                usuario_id, token_hash, token_prefix, estado, expires_at, created_at
            ) VALUES (
                :usuario_id, :token_hash, :token_prefix, 'activo', DATE_ADD(NOW(), INTERVAL 30 DAY), NOW()
            )
        ");
        $insert->execute([
            'usuario_id' => $user['id'],
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => substr($plainToken, 0, 14),
        ]);

        ApiResponse::success([
            'token' => $plainToken,
            'usuario' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'nombres' => $user['nombres'],
                'apellidos' => $user['apellidos'],
                'rol' => $user['rol_nombre'],
            ],
        ]);
    }

    public function registerDevice(): void
    {
        $token = $this->authMobile();
        $body = json_decode(file_get_contents('php://input'), true) ?: [];

        (new MobileDevice())->register([
            'usuario_id' => $token['usuario_id'],
            'platform' => $body['platform'] ?? 'unknown',
            'device_uuid' => $body['device_uuid'] ?? ('unknown-' . bin2hex(random_bytes(6))),
            'device_name' => $body['device_name'] ?? null,
            'app_version' => $body['app_version'] ?? null,
            'push_token' => $body['push_token'] ?? null,
        ]);

        ApiResponse::success(['message' => 'Dispositivo registrado.']);
    }

    public function me(): void
    {
        $token = $this->authMobile();

        $stmt = Database::connection()->prepare("
            SELECT id, nombres, apellidos, email, cargo, estado
            FROM usuarios
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $token['usuario_id']]);
        $user = $stmt->fetch();

        ApiResponse::success(['usuario' => $user]);
    }

    private function authMobile(): array
    {
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\\s+(.+)/', $authorization, $matches)) {
            ApiResponse::error('Token móvil requerido.', 401);
        }

        $hash = hash('sha256', trim($matches[1]));

        $stmt = Database::connection()->prepare("
            SELECT *
            FROM mobile_tokens
            WHERE token_hash = :hash
              AND estado = 'activo'
              AND expires_at >= NOW()
            LIMIT 1
        ");
        $stmt->execute(['hash' => $hash]);
        $token = $stmt->fetch();

        if (!$token) {
            ApiResponse::error('Token móvil inválido o vencido.', 401);
        }

        Database::connection()->prepare("UPDATE mobile_tokens SET last_used_at = NOW() WHERE id = :id")
            ->execute(['id' => $token['id']]);

        return $token;
    }
}
