# Fase 10 - Modo sin conexión y sincronización

## Componentes implementados

- PWA con manifest.
- Service Worker.
- Página offline.
- IndexedDB.
- Cola offline.
- Interceptor de formularios offline.
- API de recepción de sincronización.
- Panel de sincronización.
- Registro de eventos.
- Base para manejo de conflictos.

## Funcionamiento

Cuando el usuario intenta enviar un formulario habilitado para offline y no tiene conexión, el navegador guarda el payload en IndexedDB. Cuando vuelve internet, el sistema intenta enviar los registros pendientes al servidor.

## Archivos principales

- `public/sw.js`
- `public/manifest.json`
- `public/offline.html`
- `public/assets/js/offline/indexeddb.js`
- `public/assets/js/offline/sync-manager.js`
- `app/Controllers/ApiSyncController.php`
- `app/Controllers/SincronizacionController.php`

## Consideraciones para producción

- Requiere HTTPS.
- Validar tiempo de sesión frente a sincronización offline.
- Definir reglas de resolución de conflictos.
- Cifrar datos sensibles almacenados localmente.
- Evitar almacenar contraseñas o información crítica en IndexedDB.
- Implementar limpieza de cola local después de sincronizar.
