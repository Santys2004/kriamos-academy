# Arquitectura app móvil Kriamos BioGestión ICA

## Capas

1. Interfaz móvil Ionic.
2. Servicios Angular.
3. Almacenamiento local con Capacitor Preferences.
4. API móvil PHP.
5. Base de datos central.
6. Cola offline y sincronización.

## Flujo de autenticación

1. Usuario ingresa correo corporativo y contraseña.
2. La app llama `/api/mobile/v1/login`.
3. El servidor genera token móvil de 30 días.
4. La app guarda token localmente.
5. Las siguientes solicitudes usan Bearer Token.

## Modo offline

La app guarda registros en cola local cuando no hay conexión. Al recuperar internet, sincroniza contra endpoints del sistema.

## Evidencias

La app queda preparada para usar cámara, filesystem y subida de evidencias mediante Capacitor.
