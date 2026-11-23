<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\User;

class AuthService
{
    private array $config;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/auth.php';
    }

    public function attempt(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (!$this->isCorporateEmail($email)) {
            (new LoginAttempt())->record($email, $ip, $userAgent, false, 'Dominio corporativo no autorizado');
            return ['ok' => false, 'message' => 'El correo ingresado no pertenece al dominio corporativo autorizado.'];
        }

        $attemptModel = new LoginAttempt();

        if ($attemptModel->isLocked($email, $ip, (int) $this->config['max_login_attempts'], (int) $this->config['login_lock_minutes'])) {
            return ['ok' => false, 'message' => 'La cuenta o dirección IP está bloqueada temporalmente por intentos fallidos. Intente más tarde.'];
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || $user['estado'] !== 'activo') {
            $attemptModel->record($email, $ip, $userAgent, false, 'Usuario inexistente o inactivo');
            return ['ok' => false, 'message' => 'Credenciales inválidas o usuario no autorizado.'];
        }

        if (empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            $attemptModel->record($email, $ip, $userAgent, false, 'Contraseña incorrecta');
            $userModel->increaseFailedAttempts((int) $user['id']);
            return ['ok' => false, 'message' => 'Credenciales inválidas o usuario no autorizado.'];
        }

        $attemptModel->record($email, $ip, $userAgent, true, 'Ingreso exitoso');
        $userModel->resetFailedAttempts((int) $user['id']);
        $userModel->markLastLogin((int) $user['id']);

        $userFull = $userModel->findWithRoleAndPermissions((int) $user['id']);
        Auth::login($userFull ?: $user);

        return ['ok' => true, 'message' => 'Ingreso exitoso.'];
    }

    private function isCorporateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $domain = strtolower((string) $this->config['corporate_domain']);
        return str_ends_with($email, '@' . $domain);
    }
}
