<?php

namespace App\Services;

class HealthCheckService
{
    public function run(): array
    {
        $checks = [];

        $checks[] = $this->checkPhpVersion();
        $checks[] = $this->checkDatabase();
        $checks[] = $this->checkWritable('storage/logs');
        $checks[] = $this->checkWritable('storage/backups');
        $checks[] = $this->checkWritable('public/uploads/documentos');
        $checks[] = $this->checkWritable('public/uploads/evidencias');
        $checks[] = $this->checkHttps();
        $checks[] = $this->checkDebugMode();

        $ok = count(array_filter($checks, fn($c) => !$c['ok'])) === 0;

        return [
            'ok' => $ok,
            'checks' => $checks,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function checkPhpVersion(): array
    {
        return [
            'name' => 'Versión PHP',
            'ok' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'value' => PHP_VERSION,
            'expected' => 'PHP 8.1 o superior',
        ];
    }

    private function checkDatabase(): array
    {
        try {
            Database::connection()->query('SELECT 1');
            return [
                'name' => 'Conexión base de datos',
                'ok' => true,
                'value' => 'Conectado',
                'expected' => 'Conexión activa',
            ];
        } catch (\Throwable $e) {
            return [
                'name' => 'Conexión base de datos',
                'ok' => false,
                'value' => $e->getMessage(),
                'expected' => 'Conexión activa',
            ];
        }
    }

    private function checkWritable(string $path): array
    {
        $absolute = dirname(__DIR__, 2) . '/' . $path;
        return [
            'name' => 'Permisos escritura: ' . $path,
            'ok' => is_dir($absolute) && is_writable($absolute),
            'value' => is_dir($absolute) ? (is_writable($absolute) ? 'Escribible' : 'No escribible') : 'No existe',
            'expected' => 'Carpeta existente y escribible',
        ];
    }

    private function checkHttps(): array
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        return [
            'name' => 'HTTPS',
            'ok' => $isHttps || ($_SERVER['HTTP_HOST'] ?? '') === 'localhost',
            'value' => $isHttps ? 'Activo' : 'No detectado',
            'expected' => 'HTTPS activo en producción',
        ];
    }

    private function checkDebugMode(): array
    {
        require_once dirname(__DIR__, 2) . '/config/env.php';
        $debug = env_value('APP_DEBUG', 'false');

        return [
            'name' => 'Modo debug',
            'ok' => $debug === 'false' || env_value('APP_ENV', 'local') !== 'production',
            'value' => $debug,
            'expected' => 'false en producción',
        ];
    }
}
