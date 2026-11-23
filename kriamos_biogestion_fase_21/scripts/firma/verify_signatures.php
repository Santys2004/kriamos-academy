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

$db = App\Services\Database::connection();
$rows = $db->query("SELECT id FROM firmas_avanzadas ORDER BY id ASC")->fetchAll();

$service = new App\Services\Signature\SignatureVerificationService();
$result = [
    'total' => 0,
    'validas' => 0,
    'errores' => 0,
    'alteradas' => 0,
];

foreach ($rows as $row) {
    $result['total']++;
    $verify = $service->verify((int) $row['id']);

    if (($verify['estado'] ?? '') === 'valida') {
        $result['validas']++;
    } elseif (($verify['estado'] ?? '') === 'alterada') {
        $result['alteradas']++;
    } else {
        $result['errores']++;
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit($result['alteradas'] > 0 || $result['errores'] > 0 ? 1 : 0);
