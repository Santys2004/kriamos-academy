# Fase 20 - Firma electrónica avanzada y sellado de tiempo

## Propósito

Agregar una capa técnica reforzada de firma electrónica avanzada para formatos y documentos del sistema.

## Componentes

- Certificados internos por usuario.
- OTP de firma.
- Hash SHA-256 del objeto firmado.
- Evidencia JSON de firma.
- Sello de tiempo interno.
- Verificación de integridad.
- Historial y exportación CSV.

## Rutas

- `/firma-avanzada`
- `/firma-avanzada/certificados`
- `/firma-avanzada/solicitar-otp`
- `/firma-avanzada/firmar-formato`
- `/firma-avanzada/firmar-documento`
- `/firma-avanzada/verificar`
- `/firma-avanzada/csv`

## Consideración legal

Esta fase no sustituye la revisión jurídica ni la contratación de un proveedor certificado cuando Kriamos requiera firma electrónica con valor probatorio reforzado frente a terceros. Es una base técnica interna preparada para integración con TSA o proveedor externo.
