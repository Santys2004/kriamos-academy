<?php

namespace App\Middleware;

use App\Services\Auth;

class PermissionMiddleware
{
    public static function handle(string $permissionCode): void
    {
        if (!Auth::check()) {
            redirect('/login');
            exit;
        }

        if (!Auth::can($permissionCode)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }
    }
}
