<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Auditoria;
use App\Models\EstadoDocumental;
use App\Models\Granja;
use App\Models\Reporte;
use App\Models\TipoFormato;
use App\Services\Auth;
use App\Services\ExportService;

class ReporteController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.ver');
        $pageTitle = 'Reportes';
        require dirname(__DIR__) . '/Views/reportes/index.php';
    }

    public function formatos(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.ver');

        $pageTitle = 'Reporte de formatos';
        $filters = $this->filters();
        $model = new Reporte();
        $registros = $model->formatos($filters);
        $resumenGranjas = $model->resumenPorGranja($filters);
        $granjas = (new Granja())->active();
        $tipos = (new TipoFormato())->all();
        $estados = (new EstadoDocumental())->all();

        (new Auditoria())->record(Auth::id(), 'reportes', 'ver_formatos', 'formatos_registros', null, null, $filters);
        require dirname(__DIR__) . '/Views/reportes/formatos.php';
    }

    public function formatosCsv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.exportar');

        $filters = $this->filters();
        $rows = (new Reporte())->formatos($filters);

        (new Reporte())->registrarHistorial([
            'usuario_id' => Auth::id(),
            'tipo_reporte' => 'formatos_csv',
            'filtros_json' => json_encode($filters, JSON_UNESCAPED_UNICODE),
            'formato_salida' => 'csv',
            'archivo_path' => null,
        ]);

        ExportService::csv('reporte_formatos_kriamos_' . date('Ymd_His') . '.csv', [
            'codigo_consecutivo' => 'Consecutivo',
            'tipo_nombre' => 'Formato',
            'granja_sigla' => 'Granja',
            'galpon_codigo' => 'Galpón',
            'ciclo_codigo' => 'Ciclo',
            'fecha_formato' => 'Fecha',
            'estado_nombre' => 'Estado',
            'usuario_email' => 'Usuario',
            'es_no_aplica' => 'No aplica',
            'motivo_no_aplica' => 'Motivo No aplica',
            'observaciones' => 'Observaciones',
            'hash_documento' => 'Hash',
        ], $rows);
    }

    public function alertas(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.ver');
        $pageTitle = 'Reporte de alertas';
        $filters = $this->filters();
        $granjas = (new Granja())->active();
        $alertas = (new Reporte())->alertas($filters);
        require dirname(__DIR__) . '/Views/reportes/alertas.php';
    }

    public function alertasCsv(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.exportar');

        $rows = (new Reporte())->alertas($this->filters());
        ExportService::csv('reporte_alertas_kriamos_' . date('Ymd_His') . '.csv', [
            'prioridad' => 'Prioridad',
            'tipo' => 'Tipo',
            'titulo' => 'Título',
            'mensaje' => 'Mensaje',
            'granja_sigla' => 'Granja',
            'galpon_codigo' => 'Galpón',
            'estado' => 'Estado',
            'fecha_limite' => 'Fecha límite',
            'created_at' => 'Creada',
        ], $rows);
    }

    public function certificaciones(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.ver');
        $pageTitle = 'Reporte de certificaciones';
        $certificaciones = (new Reporte())->certificaciones();
        require dirname(__DIR__) . '/Views/reportes/certificaciones.php';
    }

    public function incidencias(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.ver');
        $pageTitle = 'Reporte de incidencias';
        $filters = $this->filters();
        $incidencias = (new Reporte())->incidencias($filters);
        require dirname(__DIR__) . '/Views/reportes/incidencias.php';
    }

    public function paqueteIca(): void
    {
        AuthMiddleware::handle();
        PermissionMiddleware::handle('reportes.exportar');

        $filters = $this->filters();
        $registros = (new Reporte())->formatos($filters);

        $manifest = [
            'empresa' => 'Kriamos',
            'sistema' => 'Kriamos BioGestión ICA',
            'generado_por_usuario_id' => Auth::id(),
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'filtros' => $filters,
            'total_registros' => count($registros),
            'nota' => 'Paquete documental inicial. En Fase 09 se anexarán documentos y evidencias del núcleo documental.',
        ];

        $csv = "Consecutivo;Formato;Granja;Galpon;Ciclo;Fecha;Estado;Usuario\n";
        foreach ($registros as $row) {
            $csv .= implode(';', [
                $row['codigo_consecutivo'] ?? '',
                $row['tipo_nombre'] ?? '',
                $row['granja_sigla'] ?? '',
                $row['galpon_codigo'] ?? '',
                $row['ciclo_codigo'] ?? '',
                $row['fecha_formato'] ?? '',
                $row['estado_nombre'] ?? '',
                $row['usuario_email'] ?? '',
            ]) . "\n";
        }

        (new Reporte())->registrarHistorial([
            'usuario_id' => Auth::id(),
            'tipo_reporte' => 'paquete_ica',
            'filtros_json' => json_encode($filters, JSON_UNESCAPED_UNICODE),
            'formato_salida' => 'zip',
            'archivo_path' => null,
        ]);

        ExportService::zipPackage('paquete_documental_ica_kriamos_' . date('Ymd_His') . '.zip', [
            'MANIFIESTO_PAQUETE_ICA.json' => json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'reporte_formatos.csv' => $csv,
            'LEEME.txt' => "Paquete documental generado por Kriamos BioGestión ICA.\n",
        ]);
    }

    private function filters(): array
    {
        return [
            'granja_id' => $_GET['granja_id'] ?? '',
            'tipo_formato_id' => $_GET['tipo_formato_id'] ?? '',
            'estado_documental_id' => $_GET['estado_documental_id'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? '',
        ];
    }
}
