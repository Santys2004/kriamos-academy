# Fase 15 - Tablero gerencial BI

## Propósito

Consolidar indicadores clave para seguimiento gerencial del cumplimiento documental, sanitario, alertas, calidad de datos y uso del sistema.

## Indicadores incluidos

- Total de formatos.
- Formatos cerrados/aprobados.
- Formatos pendientes.
- Formatos atrasados.
- Tasa de compleción documental.
- No aplica.
- Cumplimiento por granja.
- Alertas por prioridad.
- Incidencias por nivel.
- Mortalidad diaria consolidada.
- Certificaciones vencidas y próximas a vencer.
- Uso del sistema por día.

## Rutas

- `/bi`
- `/bi/indicadores`
- `/bi/export-csv`
- `/bi/snapshot`
- `/bi/snapshots`

## Snapshot BI

Permite guardar un corte histórico de indicadores para seguimiento mensual o auditorías internas.

## Recomendación futura

Para analítica avanzada se recomienda conectar Power BI, Metabase, Apache Superset o Looker Studio a vistas SQL de solo lectura.
