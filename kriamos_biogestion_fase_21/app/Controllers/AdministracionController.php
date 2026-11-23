<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\ParametroSistema;
use App\Models\Rol;
use App\Models\UsuarioAdmin;

class AdministracionController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('administracion.ver');

        $pageTitle = 'Administración avanzada';
        $metricas = [
            'usuarios' => count((new UsuarioAdmin())->all()),
            'roles' => count((new Rol())->all()),
            'parametros' => count((new ParametroSistema())->all()),
        ];

        require dirname(__DIR__) . '/Views/usuarios/admin_index.php';
    }
}
