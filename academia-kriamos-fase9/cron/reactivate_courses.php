<?php
// Ejecutar diariamente para actualizar estados de certificados y calendario.
// Ejemplo: 10 0 * * * /usr/bin/php /ruta/academia-kriamos/cron/reactivate_courses.php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/CalendarService.php';

$app = app_config();
date_default_timezone_set($app['timezone'] ?? 'America/Bogota');

try {
    $pdo = Database::connection();
    $service = new CalendarService();
    $synced = $service->syncAllCertificates($pdo);
    $service->refreshCalendarStatuses($pdo);
    echo '[' . date('Y-m-d H:i:s') . "] Calendario actualizado. Certificados sincronizados: {$synced}" . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
