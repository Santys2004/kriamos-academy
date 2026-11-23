# Fase 13 - Administración avanzada

## Propósito

Permitir que Kriamos administre usuarios, roles, permisos, parámetros y auditoría desde la interfaz del sistema.

## Componentes

- Usuarios.
- Roles y permisos.
- Parámetros.
- Auditoría consultable.
- Exportación CSV de auditoría.

## Seguridad

Solo el Administrador del sistema debe tener acceso completo a este módulo. Todo cambio crítico debe registrarse en auditoría.

## Parámetros iniciales

- Dominio corporativo.
- Intentos máximos de login.
- Minutos de bloqueo.
- Días de atraso de formatos.
- Días críticos de certificación.
- Tamaño máximo de archivos.
- Extensiones permitidas.
- Nombre de empresa.
- Versión actual del sistema.

## Recomendación

Antes de producción, validar roles reales de Kriamos y dejar solo permisos estrictamente necesarios por cada perfil.
