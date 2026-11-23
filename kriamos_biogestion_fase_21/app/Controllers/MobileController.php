<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\MobileDevice;

class MobileController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('mobile.ver');

        $pageTitle = 'App móvil';
        $devices = (new MobileDevice())->all();
        $metricas = [
            'activos' => (new MobileDevice())->countActive(),
        ];

        require dirname(__DIR__) . '/Views/mobile/index.php';
    }
}
