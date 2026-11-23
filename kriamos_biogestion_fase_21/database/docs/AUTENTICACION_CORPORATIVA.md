# Autenticación corporativa - Fase 03

## Regla principal

El sistema solo permite acceso a usuarios registrados en la tabla `usuarios`, con estado `activo` y correo perteneciente al dominio definido en `.env`:

`CORPORATE_DOMAIN=kriamos.com.co`

## Flujo de ingreso

1. Usuario ingresa correo y contraseña.
2. El sistema valida token CSRF.
3. El sistema valida formato del correo.
4. El sistema valida dominio corporativo.
5. El sistema revisa bloqueo temporal por intentos fallidos.
6. El sistema busca el usuario en base de datos.
7. El sistema verifica estado activo.
8. El sistema valida contraseña con `password_verify()`.
9. Si es correcto, crea sesión segura.
10. Si falla, registra el intento.

## Seguridad incluida

- Sesiones con cookie `HttpOnly`.
- `SameSite=Lax`.
- Regeneración de ID de sesión al ingresar.
- Validación CSRF.
- Registro de intentos.
- Bloqueo temporal por intentos fallidos.
- Roles y permisos.
- Middleware de autenticación.
- Middleware de permisos.

## Pendiente para producción

- HTTPS obligatorio.
- Doble factor de autenticación.
- Recuperación de contraseña por correo.
- Política avanzada de contraseñas.
- Auditoría ampliada.
- Cierre automático por inactividad.
