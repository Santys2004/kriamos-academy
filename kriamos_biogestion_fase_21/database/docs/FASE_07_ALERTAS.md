# Fase 07 - Alertas y vencimientos

## Objetivo

Controlar atrasos, vencimientos y eventos críticos mediante alertas internas y notificaciones.

## Reglas implementadas

### 1. Formatos atrasados
Genera alerta cuando un formato supera el plazo máximo de 3 días sin cierre o aprobación.

### 2. Certificaciones ICA
Genera alertas a 90, 60, 30, 15 y 0 días antes del vencimiento.

### 3. Acciones correctivas vencidas
Revisa formatos de hallazgos y acciones correctivas. Si la fecha límite venció y la acción no está cerrada, genera alerta.

### 4. Incidencias de calidad
Genera alertas para incidencias abiertas de nivel alto o crítico.

### 5. Productos vencidos
Revisa formatos de vacunación, medicamentos, biológicos y plaguicidas con fecha de vencimiento anterior a la fecha actual.

## Estados de alerta

- pendiente
- enviada
- leida
- atendida
- cerrada

## Prioridades

- baja
- media
- alta
- critica

## Envío por correo

La clase `MailNotificationService` queda preparada usando `mail()` de PHP. En producción se recomienda implementar SMTP con PHPMailer o Symfony Mailer.

## Ejecución programada

En producción debe configurarse un cron job o tarea programada para ejecutar la generación de alertas periódicamente.

Ejemplo conceptual:

`php public/index.php alertas:generar`

En esta fase se deja disponible la ruta web protegida:

`/alertas/generar`
