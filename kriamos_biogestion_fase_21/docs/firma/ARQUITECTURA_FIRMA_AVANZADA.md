# Arquitectura de firma avanzada

## Flujo

1. Usuario solicita OTP.
2. Sistema genera OTP con vigencia corta.
3. Usuario firma formato/documento con OTP.
4. Sistema calcula hash SHA-256 del objeto.
5. Se construye evidencia JSON.
6. Se calcula hash de firma.
7. Se genera sello de tiempo interno.
8. Se registra auditoría.

## Verificación

1. Se recalcula hash actual del objeto.
2. Se compara contra hash firmado.
3. Se valida existencia de sello.
4. Se marca como válida, alterada, sin sello o error.

## Integración futura

- Proveedor de firma electrónica.
- TSA externa.
- Certificados digitales X.509.
- Validación OCSP/CRL.
- Firma PAdES para PDF.
