<?php

namespace App\Controllers\Api;

use App\Models\BiExternalLog;
use App\Services\Api\ApiResponse;
use App\Services\BIExternal\BiCsvExportService;
use App\Services\BIExternal\BiDatasetQueryService;
use App\Services\BIExternal\BiExternalAuthService;

class BiExternalApiController
{
    private float $startedAt;

    public function dataset(): void
    {
        $this->startedAt = microtime(true);

        $auth = new BiExternalAuthService();
        $connection = $auth->authenticate();

        if (!$connection) {
            ApiResponse::error('Token BI inválido, inactivo o IP no autorizada.', 401);
        }

        $codigo = $_GET['dataset'] ?? '';
        $format = $_GET['format'] ?? 'json';

        if ($codigo === '') {
            $this->log($connection, $codigo, $format, 422, 0);
            ApiResponse::error('Debe indicar el parámetro dataset.', 422);
        }

        $result = (new BiDatasetQueryService())->query($codigo, [
            'limit' => $_GET['limit'] ?? 5000,
        ]);

        if (!$result['ok']) {
            $this->log($connection, $codigo, $format, 404, 0);
            ApiResponse::error($result['message'], 404);
        }

        $rows = $result['rows'];
        $this->log($connection, $codigo, $format, 200, count($rows));

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $codigo . '_' . date('Ymd_His') . '.csv"');
            echo (new BiCsvExportService())->arrayToCsv($rows);
            exit;
        }

        ApiResponse::success([
            'dataset' => $codigo,
            'rows' => $rows,
            'total' => count($rows),
            'generated_at' => date('c'),
        ]);
    }

    public function catalog(): void
    {
        $this->startedAt = microtime(true);

        $auth = new BiExternalAuthService();
        $connection = $auth->authenticate();

        if (!$connection) {
            ApiResponse::error('Token BI inválido, inactivo o IP no autorizada.', 401);
        }

        $datasets = (new \App\Models\BiExternalDataset())->active();
        $this->log($connection, 'catalog', 'json', 200, count($datasets));

        ApiResponse::success([
            'datasets' => $datasets,
            'total' => count($datasets),
        ]);
    }

    private function log(array $connection, string $dataset, string $format, int $status, int $rows): void
    {
        $ms = (int) round((microtime(true) - $this->startedAt) * 1000);

        (new BiExternalLog())->create([
            'connection_id' => $connection['id'],
            'dataset_codigo' => $dataset,
            'formato' => $format,
            'status_code' => $status,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'response_rows' => $rows,
            'response_ms' => $ms,
        ]);
    }
}
