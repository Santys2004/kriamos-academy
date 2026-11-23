<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController
{
    private DashboardService $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function index(): void
    {
        $filters = $this->service->filtersFromRequest($_GET);
        try {
            $pdo = Database::connection();
            $dashboard = $this->service->getDashboard($pdo, $filters);
            view('dashboard', [
                'title' => 'Dashboard gerencial',
                'filters' => $filters,
                'dashboard' => $dashboard,
                'error' => null,
            ]);
        } catch (Throwable $e) {
            view('dashboard', [
                'title' => 'Dashboard gerencial',
                'filters' => $filters,
                'dashboard' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function export(): void
    {
        $filters = $this->service->filtersFromRequest($_GET);
        try {
            $pdo = Database::connection();
            $csv = $this->service->exportCsv($pdo, $filters);
            $filename = 'dashboard_academia_kriamos_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo "\xEF\xBB\xBF";
            echo $csv;
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error al generar exportación: ' . $e->getMessage();
        }
    }
}
