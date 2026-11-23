<?php

$root = dirname(__DIR__);
$paths = [
    'storage/logs',
    'storage/backups',
    'storage/health',
    'storage/security',
    'public/uploads/documentos',
    'public/uploads/evidencias',
    'public/uploads/firmas',
];

echo "Verificación de permisos Kriamos BioGestión ICA\n";
echo "================================================\n";

$ok = true;

foreach ($paths as $path) {
    $full = $root . '/' . $path;

    if (!is_dir($full)) {
        mkdir($full, 0775, true);
    }

    $writable = is_writable($full);
    echo $path . ': ' . ($writable ? 'OK' : 'NO ESCRIBIBLE') . PHP_EOL;

    if (!$writable) {
        $ok = false;
    }
}

exit($ok ? 0 : 1);
