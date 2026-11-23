<?php

require_once dirname(__DIR__, 2) . '/config/env.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__, 2) . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$expedienteId = (int) ($argv[1] ?? 0);

if ($expedienteId <= 0) {
    echo "Uso: php scripts/ica/generate_ica_package.php ID_EXPEDIENTE\n";
    exit(1);
}

$result = (new App\Services\IcaAudit\IcaPackageService())->generate($expedienteId);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit(($result['ok'] ?? false) ? 0 : 1);
