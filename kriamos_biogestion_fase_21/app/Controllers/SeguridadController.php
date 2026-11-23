<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\SecurityEvent;
use App\Services\HealthCheckService;

class SeguridadController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('seguridad.ver');

        $pageTitle = 'Seguridad del sistema';
        $eventModel = new SecurityEvent();
        $eventos = $eventModel->recientes();
        $metricas = [
            'criticos' => $eventModel->countByNivel('critico'),
            'altos' => $eventModel->countByNivel('alto'),
            'medios' => $eventModel->countByNivel('medio'),
            'bajos' => $eventModel->countByNivel('bajo'),
        ];

        require dirname(__DIR__) . '/Views/seguridad/index.php';
    }

    public function health(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('seguridad.ver');

        $pageTitle = 'Salud del sistema';
        $health = (new HealthCheckService())->run();

        require dirname(__DIR__) . '/Views/seguridad/health.php';
    }
}
