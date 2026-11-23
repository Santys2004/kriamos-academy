# Fase 12 - Seguridad, pruebas y despliegue

## Componentes incluidos

- Middleware de cabeceras de seguridad.
- Panel de seguridad.
- Health check.
- Checklist de despliegue.
- Scripts de backup.
- Scripts de verificación de permisos.
- Scripts de prueba básica de rutas.
- Configuración de ejemplo para Apache y Nginx.
- `.env.production.example`.

## Seguridad mínima en producción

1. Activar HTTPS.
2. Usar APP_DEBUG=false.
3. Cambiar contraseña temporal del administrador.
4. Usar usuario de base de datos sin privilegios globales.
5. Restringir acceso por dominio corporativo.
6. Programar backups.
7. Monitorear health check.
8. Revisar logs de seguridad.
9. Mantener servidor actualizado.
10. Capacitar usuarios.

## Backups recomendados

- Base de datos: diario.
- Archivos cargados: diario.
- Retención mínima: 30 días.
- Copia externa: nube o servidor alterno.
- Prueba de restauración: mensual.

## Despliegue recomendado

- Linux Ubuntu Server LTS.
- Nginx o Apache.
- PHP-FPM 8.1+.
- MariaDB/MySQL.
- Certificado SSL.
- Backups automatizados.
- Monitoreo 24/7.
