<?php

require_once dirname(__DIR__) . '/config/env.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$filters = [
    'fecha_inicio' => date('Y-m-01'),
    'fecha_fin' => date('Y-m-d'),
    'granja_id' => '',
];

$data = (new App\Services\BI\BiDashboardService())->build($filters);

$id = (new App\Models\BiIndicador())->createSnapshot([
    'fecha_snapshot' => date('Y-m-d'),
    'usuario_id' => null,
    'filtros_json' => json_encode($filters, JSON_UNESCAPED_UNICODE),
    'indicadores_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
]);

echo "Snapshot BI generado: $id\n";
exit(0);
