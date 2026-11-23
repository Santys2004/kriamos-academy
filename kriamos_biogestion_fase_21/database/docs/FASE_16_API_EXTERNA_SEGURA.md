# Fase 16 - API externa segura

## Propósito

Permitir integraciones controladas con sistemas internos o herramientas externas autorizadas por Kriamos.

## Seguridad

- Bearer Token.
- Token almacenado como hash SHA-256.
- Token visible solo al momento de creación.
- Revocación de tokens.
- Vencimiento opcional.
- Restricción opcional por IP.
- Rate limit básico diario.
- Logs de consumo.

## Endpoints

- `GET /api/v1/health`
- `GET /api/v1/granjas`
- `GET /api/v1/galpones`
- `GET /api/v1/formatos`
- `GET /api/v1/alertas`
- `GET /api/v1/documentos`
- `GET /api/v1/bi`

## Cabecera requerida

`Authorization: Bearer TOKEN`

## Documentación OpenAPI

Archivo:

`docs/api/openapi_kriamos_v1.yaml`

## Recomendaciones

- Usar HTTPS obligatorio.
- Rotar tokens periódicamente.
- No compartir tokens por canales inseguros.
- Integrar rate limit por Redis o gateway API en producción.
- Crear tokens distintos por sistema consumidor.
