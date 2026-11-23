<?php

require_once __DIR__ . '/env.php';

return [
    'format_delay_days' => (int) env_value('ALERT_DAYS_FORMAT_DELAY', 3),
    'certification_days' => [
        (int) env_value('ALERT_CERT_DAYS_90', 90),
        (int) env_value('ALERT_CERT_DAYS_60', 60),
        (int) env_value('ALERT_CERT_DAYS_30', 30),
        (int) env_value('ALERT_CERT_DAYS_15', 15),
        0,
    ],
    'mail' => [
        'host' => env_value('MAIL_HOST', ''),
        'port' => env_value('MAIL_PORT', '587'),
        'username' => env_value('MAIL_USERNAME', ''),
        'password' => env_value('MAIL_PASSWORD', ''),
        'from_address' => env_value('MAIL_FROM_ADDRESS', 'notificaciones@kriamos.com.co'),
        'from_name' => env_value('MAIL_FROM_NAME', 'Kriamos BioGestión ICA'),
    ],
];
