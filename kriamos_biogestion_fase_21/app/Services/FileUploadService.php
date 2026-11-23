<?php

namespace App\Services;

class FileUploadService
{
    private array $config;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/files.php';
    }

    public function validate(array $file): array
    {
        if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'No se recibió un archivo válido.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->config['allowed_extensions'], true)) {
            return ['ok' => false, 'message' => 'Extensión no permitida: ' . $extension];
        }

        $maxBytes = (int) $this->config['max_upload_mb'] * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            return ['ok' => false, 'message' => 'El archivo supera el tamaño máximo permitido.'];
        }

        $mime = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? 'application/octet-stream');
        if (!in_array($mime, $this->config['allowed_mime_types'], true)) {
            return ['ok' => false, 'message' => 'Tipo MIME no permitido: ' . $mime];
        }

        return ['ok' => true, 'extension' => $extension, 'mime_type' => $mime];
    }

    public function store(array $file, string $prefix = 'doc'): array
    {
        $validation = $this->validate($file);
        if (!$validation['ok']) {
            return $validation;
        }

        $dir = $this->config['public_upload_path'] . '/' . date('Y/m');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $safeName = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $validation['extension'];
        $absolutePath = $dir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return ['ok' => false, 'message' => 'No fue posible guardar el archivo en el servidor.'];
        }

        $relativeUrl = $this->config['public_upload_url'] . '/' . date('Y/m') . '/' . $safeName;

        return [
            'ok' => true,
            'archivo_path' => $relativeUrl,
            'absolute_path' => $absolutePath,
            'mime_type' => $validation['mime_type'],
            'extension' => $validation['extension'],
            'peso_bytes' => (int) $file['size'],
            'original_name' => $file['name'],
        ];
    }

    public function extensionToTipo(string $extension): string
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg', 'png', 'webp' => 'foto',
            'mp4', 'mov' => 'video',
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx', 'csv' => 'excel',
            default => 'otro',
        };
    }
}
