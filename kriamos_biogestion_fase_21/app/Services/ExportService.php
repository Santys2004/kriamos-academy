<?php

namespace App\Services;

class ExportService
{
    public static function csv(string $filename, array $headers, array $rows): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, array_values($headers), ';');

        foreach ($rows as $row) {
            $line = [];
            foreach (array_keys($headers) as $key) {
                $line[] = is_array($row[$key] ?? '') ? json_encode($row[$key], JSON_UNESCAPED_UNICODE) : ($row[$key] ?? '');
            }
            fputcsv($output, $line, ';');
        }
        fclose($output);
        exit;
    }

    public static function zipPackage(string $filename, array $metadataFiles = []): void
    {
        $tmpPath = sys_get_temp_dir() . '/' . uniqid('kriamos_package_', true) . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($tmpPath, \ZipArchive::CREATE) !== true) {
            http_response_code(500);
            exit('No fue posible generar el paquete documental.');
        }

        foreach ($metadataFiles as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpPath));

        readfile($tmpPath);
        unlink($tmpPath);
        exit;
    }
}
