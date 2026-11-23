<?php

namespace App\Services\IcaAudit;

use App\Models\IcaExportacion;
use App\Models\IcaExpediente;
use App\Models\IcaHallazgo;
use App\Models\IcaMatrizDocumento;
use App\Models\IcaPlanAccion;
use App\Services\Auth;

class IcaPackageService
{
    public function generate(int $expedienteId): array
    {
        $expediente = (new IcaExpediente())->find($expedienteId);

        if (!$expediente) {
            return [
                'ok' => false,
                'message' => 'Expediente no encontrado.',
            ];
        }

        $dir = dirname(__DIR__, 3) . '/storage/ica_audit/paquetes/expediente_' . $expedienteId . '_' . date('Ymd_His');

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $matrix = (new IcaMatrizDocumento())->byExpediente($expedienteId);
        $hallazgos = (new IcaHallazgo())->byExpediente($expedienteId);
        $planes = (new IcaPlanAccion())->byExpediente($expedienteId);
        $resumen = (new IcaMatrizDocumento())->resumenCumplimiento($expedienteId);

        $manifest = [
            'expediente' => $expediente,
            'resumen_cumplimiento' => $resumen,
            'total_matriz' => count($matrix),
            'total_hallazgos' => count($hallazgos),
            'total_planes' => count($planes),
            'generado_at' => date('c'),
            'generado_por' => Auth::id(),
            'nota' => 'Paquete interno de auditoría. Debe ser revisado antes de presentación oficial ante ICA.',
        ];

        file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($dir . '/matriz_documental.csv', $this->csv($matrix));
        file_put_contents($dir . '/hallazgos.csv', $this->csv($hallazgos));
        file_put_contents($dir . '/planes_accion.csv', $this->csv($planes));
        file_put_contents($dir . '/README.txt', $this->readme($expediente, $resumen));

        $zipPath = $dir . '.zip';
        $this->zipDirectory($dir, $zipPath);

        $hash = hash_file('sha256', $zipPath);

        $id = (new IcaExportacion())->create([
            'expediente_id' => $expedienteId,
            'tipo' => 'paquete_zip',
            'archivo_path' => $zipPath,
            'hash_sha256' => $hash,
            'total_archivos' => 4,
            'generado_por' => Auth::id(),
            'estado' => 'generado',
        ]);

        return [
            'ok' => true,
            'message' => 'Paquete ICA generado correctamente.',
            'exportacion_id' => $id,
            'path' => $zipPath,
            'hash' => $hash,
        ];
    }

    private function csv(array $rows): string
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
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function readme(array $expediente, array $resumen): string
    {
        return "Paquete documental ICA - Kriamos BioGestión ICA\n"
            . "Expediente: " . $expediente['codigo'] . "\n"
            . "Granja: " . $expediente['granja_sigla'] . " - " . $expediente['granja_nombre'] . "\n"
            . "Cumplimiento: " . ($resumen['porcentaje_cumplimiento'] ?? 0) . "%\n\n"
            . "Este paquete es de uso interno y debe ser revisado por el responsable técnico antes de entrega oficial.\n";
    }

    private function zipDirectory(string $dir, string $zipPath): void
    {
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isDir()) {
                continue;
            }

            $zip->addFile($file->getRealPath(), substr($file->getRealPath(), strlen($dir) + 1));
        }

        $zip->close();
    }
}
