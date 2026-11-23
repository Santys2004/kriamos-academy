<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\BiIndicador;
use App\Models\Granja;
use App\Services\Auth;
use App\Services\BI\BiDashboardService;
use App\Services\ExportService;
use App\Services\Session;

class BiController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi.ver');

        $pageTitle = 'Tablero gerencial BI';
        $filters = $this->filters();

        $service = new BiDashboardService();
        $data = $service->build($filters);
        $score = $service->scoreCumplimiento($data['resumen']);
        $granjas = (new Granja())->active();

        require dirname(__DIR__) . '/Views/bi/index.php';
    }

    public function indicadores(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi.ver');

        $pageTitle = 'Indicadores BI';
        $filters = $this->filters();
        $data = (new BiDashboardService())->build($filters);
        $granjas = (new Granja())->active();

        require dirname(__DIR__) . '/Views/indicadores/index.php';
    }

    public function exportCsv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi.exportar');

        $filters = $this->filters();
        $data = (new BiDashboardService())->build($filters);

        $rows = [];
        foreach ($data['por_granja'] as $row) {
            $rows[] = [
                'granja' => $row['sigla'] . ' - ' . $row['nombre'],
                'total_formatos' => $row['total_formatos'],
                'cerrados' => $row['cerrados'],
                'pendientes' => $row['pendientes'],
                'atrasados' => $row['atrasados'],
                'no_aplica' => $row['no_aplica'],
                'tasa_complecion' => $row['tasa_complecion'],
            ];
        }

        ExportService::csv('bi_indicadores_kriamos_' . date('Ymd_His') . '.csv', [
            'granja' => 'Granja',
            'total_formatos' => 'Total formatos',
            'cerrados' => 'Cerrados',
            'pendientes' => 'Pendientes',
            'atrasados' => 'Atrasados',
            'no_aplica' => 'No aplica',
            'tasa_complecion' => 'Tasa compleción (%)',
        ], $rows);
    }

    public function snapshot(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi.gestionar');

        $filters = $this->filters();
        $data = (new BiDashboardService())->build($filters);

        $id = (new BiIndicador())->createSnapshot([
            'fecha_snapshot' => date('Y-m-d'),
            'usuario_id' => Auth::id(),
            'filtros_json' => json_encode($filters, JSON_UNESCAPED_UNICODE),
            'indicadores_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        (new Auditoria())->record(Auth::id(), 'bi', 'snapshot', 'bi_snapshots', $id, null, $filters);

        Session::flash('success', 'Snapshot BI generado correctamente.');
        redirect('/bi');
        exit;
    }

    public function snapshots(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('bi.ver');

        $pageTitle = 'Snapshots BI';
        $snapshots = (new BiIndicador())->snapshots();

        require dirname(__DIR__) . '/Views/bi/snapshots.php';
    }

    private function filters(): array
    {
        return [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'granja_id' => $_GET['granja_id'] ?? '',
        ];
    }
}
