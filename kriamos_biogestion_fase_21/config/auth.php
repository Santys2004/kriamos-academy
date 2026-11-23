<?php

require_once __DIR__ . '/env.php';

return [
    'corporate_domain' => env_value('CORPORATE_DOMAIN', 'kriamos.com.co'),
    'session_lifetime' => (int) env_value('SESSION_LIFETIME', 120),
    'max_login_attempts' => (int) env_value('MAX_LOGIN_ATTEMPTS', 5),
    'login_lock_minutes' => (int) env_value('LOGIN_LOCK_MINUTES', 15),
];
