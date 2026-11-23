<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\IncidenciaCalidad;

class CalidadController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('calidad.ver');

        $pageTitle = 'Control de calidad';
        $incidenciasAbiertas = (new IncidenciaCalidad())->abiertas();

        require dirname(__DIR__) . '/Views/calidad/index.php';
    }
}
