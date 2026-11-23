<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\SyncLog;

class SincronizacionController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('sincronizacion.ver');

        $pageTitle = 'Sincronización offline';
        $model = new SyncLog();
        $logs = $model->recientes();
        $metricas = [
            'sincronizados' => $model->countByEstado('sincronizado'),
            'pendientes' => $model->countByEstado('pendiente'),
            'error' => $model->countByEstado('error'),
            'conflicto' => $model->countByEstado('conflicto'),
        ];

        require dirname(__DIR__) . '/Views/sincronizacion/index.php';
    }
}
