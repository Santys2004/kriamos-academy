# Guía BI externa - Kriamos BioGestión ICA

## Power BI

Opción recomendada:
1. Crear conexión BI en `/bi-externo`.
2. Copiar token.
3. Usar Web connector:
   - URL: `https://dominio/api/bi/v1/dataset?dataset=formatos&format=csv`
   - Header: `Authorization: Bearer TOKEN_BI`

Opción alternativa:
- Conectar a MySQL con usuario de solo lectura.
- Usar vistas `vw_bi_*`.

## Metabase

1. Crear usuario MySQL de solo lectura.
2. Registrar base de datos en Metabase.
3. Usar vistas `vw_bi_*`.
4. Restringir acceso por red/VPN.

## Looker Studio

1. Usar conector MySQL o CSV publicado internamente.
2. Evitar exponer endpoints sin autenticación.
3. Usar tokens BI con IP permitida.

## Apache Superset

1. Crear conexión SQLAlchemy con usuario de solo lectura.
2. Registrar datasets desde vistas `vw_bi_*`.
3. Configurar permisos por tablero.

## Buenas prácticas

- No usar usuario root de base de datos.
- No exponer base de datos a internet público.
- Usar VPN o lista blanca de IP.
- Registrar auditoría de consumos.
- Rotar tokens.
