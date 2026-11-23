<?php

$routes = require dirname(__DIR__) . '/routes/web.php';

$required = [
    'GET /',
    'GET /login',
    'GET /granjas',
    'GET /formatos',
    'GET /alertas',
    'GET /reportes',
    'GET /documentos',
    'GET /sincronizacion',
    'GET /ayuda',
    'GET /seguridad',
    'GET /despliegue/checklist',
];

$missing = [];

foreach ($required as $route) {
    if (!isset($routes[$route])) {
        $missing[] = $route;
    }
}

if ($missing) {
    echo "Rutas faltantes:\n" . implode("\n", $missing) . "\n";
    exit(1);
}

echo "Todas las rutas críticas están registradas.\n";
exit(0);
