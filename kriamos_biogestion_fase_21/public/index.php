<?php

declare(strict_types=1);

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once dirname(__DIR__) . '/config/env.php';

// Detectar base path automáticamente
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_PATH', $scriptDir);

// Helper global de redirect
function redirect(string $path): void {
    $base = BASE_PATH;
    $url = $base . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

require_once dirname(__DIR__) . '/app/Middleware/SecurityHeadersMiddleware.php';
\App\Middleware\SecurityHeadersMiddleware::handle();

spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
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

$routes = require dirname(__DIR__) . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route  = '/' . trim(str_replace($scriptDir, '', $path), '/');
$route  = ($route === '' || $route === false) ? '/' : $route;
$routeKey = $method . ' ' . $route;

if (!isset($routes[$routeKey])) {
    http_response_code(404);
    require dirname(__DIR__) . '/app/Views/errors/404.php';
    exit;
}

[$controller, $methodName] = $routes[$routeKey];

$instance = new $controller();
$instance->$methodName();
