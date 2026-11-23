<?php
return [
    // En ambiente local queda en modo simulación: no envía correos reales,
    // pero deja evidencia en storage/logs/mail_preview.log.
    'enabled' => false,
    'from_email' => 'capacitaciones@kriamos.com',
    'from_name' => 'Academia KRIAMOS',
    'reply_to' => 'capacitaciones@kriamos.com',
    'transport' => 'native_mail',
];
