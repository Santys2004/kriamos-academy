<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\AuditoriaConsulta;
use App\Models\UsuarioAdmin;
use App\Services\ExportService;

class AuditoriaController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria.ver');

        $pageTitle = 'Auditoría del sistema';
        $filters = $this->filters();

        $model = new AuditoriaConsulta();
        $registros = $model->search($filters);
        $modulos = $model->modules();
        $usuarios = (new UsuarioAdmin())->all();

        require dirname(__DIR__) . '/Views/auditoria/index.php';
    }

    public function csv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('auditoria.ver');

        $rows = (new AuditoriaConsulta())->search($this->filters());

        ExportService::csv('auditoria_kriamos_' . date('Ymd_His') . '.csv', [
            'created_at' => 'Fecha',
            'email' => 'Usuario',
            'modulo' => 'Módulo',
            'accion' => 'Acción',
            'tabla_afectada' => 'Tabla',
            'registro_id' => 'Registro ID',
            'ip' => 'IP',
            'dispositivo' => 'Dispositivo',
        ], $rows);
    }

    private function filters(): array
    {
        return [
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'modulo' => $_GET['modulo'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ];
    }
}
