<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class DatabaseController
{
    public function schema(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria.ver');

        $pageTitle = 'Estructura de base de datos';
        require dirname(__DIR__) . '/Views/database/schema.php';
    }
}
