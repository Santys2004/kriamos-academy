<?php

function app_config(?string $key = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $key ? ($config[$key] ?? null) : $config;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function view(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../views/layouts/header.php';
    require __DIR__ . '/../views/pages/' . $view . '.php';
    require __DIR__ . '/../views/layouts/footer.php';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_url(string $page = 'home', array $params = []): string
{
    $query = array_merge(['page' => $page], $params);
    return 'index.php?' . http_build_query($query);
}

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
            $errors[$field] = "El campo {$label} es obligatorio.";
        }
    }
    return $errors;
}

function normalize_document(string $document): string
{
    return preg_replace('/[^0-9]/', '', $document);
}

function upload_person_photo(array $file, string $document): ?string
{
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible cargar la foto. Intente nuevamente.');
    }

    $config = app_config();

    if ($file['size'] > $config['max_photo_size']) {
        throw new RuntimeException('La foto supera el tamaño máximo permitido de 3 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $config['allowed_photo_types'], true)) {
        throw new RuntimeException('Formato de foto no permitido. Use JPG, PNG o WEBP.');
    }

    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };

    if (!is_dir($config['upload_photo_dir'])) {
        mkdir($config['upload_photo_dir'], 0775, true);
    }

    $filename = $document . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = rtrim($config['upload_photo_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('No fue posible guardar la foto en el servidor.');
    }

    return rtrim($config['upload_photo_url'], '/') . '/' . $filename;
}


function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function safe_slug(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim((string)$value, '-') ?: 'archivo';
}

function format_bytes(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    }
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

function material_icon(string $type): string
{
    return match ($type) {
        'manual' => 'fa-book-open',
        'video' => 'fa-video',
        'podcast' => 'fa-podcast',
        'pdf' => 'fa-file-pdf',
        'word' => 'fa-file-word',
        'powerpoint' => 'fa-file-powerpoint',
        'imagen' => 'fa-image',
        default => 'fa-file',
    };
}

function upload_course_material(array $file, string $courseCode, string $materialType): array
{
    if (!isset($file['tmp_name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Debe seleccionar un archivo para cargar el material.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No fue posible cargar el archivo. Intente nuevamente.');
    }

    $config = app_config();

    if ($file['size'] > $config['max_material_size']) {
        throw new RuntimeException('El archivo supera el tamaño máximo permitido de 250 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $config['allowed_material_types'], true)) {
        throw new RuntimeException('Tipo de archivo no permitido para materiales de capacitación.');
    }

    $originalName = $file['name'] ?? 'material';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($extension === '') {
        $extension = match ($mime) {
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/wav', 'audio/x-wav' => 'wav',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }

    $baseDir = rtrim($config['upload_material_dir'], DIRECTORY_SEPARATOR);
    $courseDir = $baseDir . DIRECTORY_SEPARATOR . safe_slug($courseCode);
    if (!is_dir($courseDir)) {
        mkdir($courseDir, 0775, true);
    }

    $filename = safe_slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $courseDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('No fue posible guardar el material en el servidor.');
    }

    $relativeUrl = rtrim($config['upload_material_url'], '/') . '/' . safe_slug($courseCode) . '/' . $filename;

    return [
        'path' => $relativeUrl,
        'original_filename' => $originalName,
        'mime_type' => $mime,
        'file_size' => (int)$file['size'],
        'material_type' => $materialType,
    ];
}

function mail_config(?string $key = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/mail.php';
    }
    return $key ? ($config[$key] ?? null) : $config;
}

function human_datetime(?string $value): string
{
    if (!$value) {
        return 'No definido';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y h:i a', $ts) : $value;
}

function ai_config(?string $key = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/ai.php';
    }
    return $key ? ($config[$key] ?? null) : $config;
}
