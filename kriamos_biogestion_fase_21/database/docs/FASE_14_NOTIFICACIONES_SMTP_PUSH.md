# Fase 14 - Notificaciones avanzadas, SMTP y Push

## Propósito

Fortalecer las alertas del sistema mediante notificaciones internas, cola de correos SMTP y preparación para notificaciones push PWA.

## Componentes

- Centro de notificaciones.
- Cola de correos.
- Servicio SMTP base.
- Plantillas HTML.
- Suscripciones push.
- Parámetros SMTP desde base de datos.
- Procesamiento manual y por script.

## Rutas principales

- `/notificaciones`
- `/notificaciones/correos`
- `/notificaciones/procesar-cola`
- `/notificaciones/push`
- `/api/push/subscribe`

## Script de cola

`php scripts/process_mail_queue.php`

## Consideraciones de producción

1. Usar SMTP corporativo real.
2. Configurar SPF, DKIM y DMARC.
3. Proteger contraseña SMTP.
4. Procesar cola con cron.
5. Para Web Push real, configurar VAPID.
6. Considerar PHPMailer o Symfony Mailer para robustez.
