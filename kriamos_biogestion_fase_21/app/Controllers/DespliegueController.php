<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class DespliegueController
{
    public function checklist(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('despliegue.ver');

        $pageTitle = 'Checklist de despliegue';
        $items = [
            ['area' => 'Servidor', 'item' => 'PHP 8.1 o superior instalado', 'critical' => true],
            ['area' => 'Servidor', 'item' => 'MySQL/MariaDB configurado con usuario no root', 'critical' => true],
            ['area' => 'Seguridad', 'item' => 'HTTPS activo con certificado vigente', 'critical' => true],
            ['area' => 'Seguridad', 'item' => 'APP_DEBUG=false en producción', 'critical' => true],
            ['area' => 'Seguridad', 'item' => 'Contraseña temporal admin cambiada', 'critical' => true],
            ['area' => 'Seguridad', 'item' => 'Acceso restringido solo a correos corporativos Kriamos', 'critical' => true],
            ['area' => 'Backups', 'item' => 'Backup diario de base de datos programado', 'critical' => true],
            ['area' => 'Backups', 'item' => 'Backup de archivos cargados programado', 'critical' => true],
            ['area' => 'Monitoreo', 'item' => 'Health check supervisado 24/7', 'critical' => true],
            ['area' => 'Monitoreo', 'item' => 'Logs revisados periódicamente', 'critical' => false],
            ['area' => 'Legal/ICA', 'item' => 'Formatos revisados por responsable sanitario antes de uso oficial', 'critical' => true],
            ['area' => 'Operación', 'item' => 'Usuarios, roles y permisos verificados', 'critical' => true],
            ['area' => 'Operación', 'item' => 'Capacitación a usuarios realizada', 'critical' => false],
        ];

        require dirname(__DIR__) . '/Views/despliegue/checklist.php';
    }
}
