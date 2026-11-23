<?php

namespace App\Services\BIExternal;

class BiCsvExportService
{
    public function arrayToCsv(array $rows): string
    {
        if (empty($rows)) {
            return "";
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, array_keys($rows[0]), ';');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function saveCsv(string $codigo, array $rows): string
    {
        $dir = dirname(__DIR__, 3) . '/storage/bi/external_exports';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . '/' . $codigo . '_' . date('Ymd_His') . '.csv';
        file_put_contents($path, $this->arrayToCsv($rows));

        return $path;
    }
}
