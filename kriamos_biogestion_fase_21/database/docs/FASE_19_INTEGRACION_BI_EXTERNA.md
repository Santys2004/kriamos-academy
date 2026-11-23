# Fase 19 - Integración BI externa

## Propósito

Permitir que herramientas externas de analítica consuman datos autorizados del sistema Kriamos BioGestión ICA.

## Componentes

- Panel BI externo.
- Conexiones externas autorizadas.
- Token BI.
- Restricción opcional por IP.
- Datasets.
- Vistas SQL.
- Endpoint JSON/CSV.
- Logs de consumo.

## Endpoints

- `GET /api/bi/v1/catalog`
- `GET /api/bi/v1/dataset?dataset=formatos&format=json`
- `GET /api/bi/v1/dataset?dataset=formatos&format=csv`

## Cabecera requerida

`Authorization: Bearer TOKEN_BI`

## Datasets iniciales

- granjas
- galpones
- formatos
- alertas
- certificaciones
- documentos
- indicadores_granja
- mortalidad_diaria
- uso_sistema

## Recomendación de seguridad

Para conexión directa de Power BI o Metabase a base de datos, crear usuario SQL de solo lectura y limitar por IP/VPN. Preferir endpoints API cuando sea posible.
