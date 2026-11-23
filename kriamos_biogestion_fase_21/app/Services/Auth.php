<?php

namespace App\Services;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            return null;
        }

        return (new User())->findWithRoleAndPermissions((int) $userId);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $userId = Session::get('user_id');
        return $userId ? (int) $userId : null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_name', trim($user['nombres'] . ' ' . $user['apellidos']));
        Session::set('role_name', $user['rol_nombre'] ?? '');
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function can(string $permissionCode): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        return in_array($permissionCode, $user['permisos'] ?? [], true);
    }
}
