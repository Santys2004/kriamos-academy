<?php
// Ejecutar cada día mediante cron para enviar alertas de capacitación.
// Ejemplo: 0 7 * * * /usr/bin/php /ruta/academia-kriamos/cron/send_reminders.php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/CalendarService.php';

$app = app_config();
date_default_timezone_set($app['timezone'] ?? 'America/Bogota');

try {
    $pdo = Database::connection();
    $service = new CalendarService();
    $result = $service->processDueNotifications($pdo, 200);
    echo '[' . date('Y-m-d H:i:s') . '] Recordatorios procesados: ' . json_encode($result, JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
