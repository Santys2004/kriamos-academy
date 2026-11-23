<?php
// Ejecutar diariamente para marcar certificados vencidos.
// Este script se conserva separado para auditoría técnica y puede ejecutarse junto con reactivate_courses.php.

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/CalendarService.php';

$app = app_config();
date_default_timezone_set($app['timezone'] ?? 'America/Bogota');

try {
    $pdo = Database::connection();
    $service = new CalendarService();
    $service->refreshCalendarStatuses($pdo);
    echo '[' . date('Y-m-d H:i:s') . '] Certificados y calendario revisados correctamente.' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
