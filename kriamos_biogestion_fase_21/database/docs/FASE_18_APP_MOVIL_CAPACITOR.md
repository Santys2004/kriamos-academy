# Fase 18 - App móvil nativa/híbrida con Capacitor/Ionic

## Propósito

Entregar una base móvil para Android e iOS que consuma el sistema Kriamos BioGestión ICA, con soporte para offline, evidencias, documentos, alertas y sincronización.

## Componentes

- Proyecto `mobile/` Ionic + Angular.
- Capacitor para Android/iOS.
- Servicios móviles:
  - AuthService.
  - ApiService.
  - StorageService.
  - NetworkService.
  - SyncService.
- Pantallas base:
  - Login.
  - Dashboard.
  - Formatos.
  - Alertas.
  - Documentos.
  - Sincronización.
  - IA Documental.
- Endpoints PHP móviles:
  - `/api/mobile/v1/login`
  - `/api/mobile/v1/me`
  - `/api/mobile/v1/register-device`

## Tablas

- `mobile_devices`
- `mobile_tokens`
- `mobile_sync_logs`

## Compilación

```bash
cd mobile
npm install
npm run build
npx cap add android
npx cap add ios
npx cap sync
```

## Requisitos

- Android Studio para Android.
- macOS + Xcode para iOS.
- HTTPS en producción.
- Token móvil protegido.
- Políticas de privacidad y seguridad definidas.
