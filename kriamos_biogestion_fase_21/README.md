# Kriamos BioGestión ICA - Fase 21

## Objetivo

Implementar la integración directa con flujos de auditoría ICA, permitiendo preparar expedientes documentales por granja, consolidar evidencias, registrar hallazgos, gestionar planes de acción y generar paquetes internos de auditoría para revisión previa a presentación ante la autoridad competente.

## Alcance incluido

- Panel de auditoría ICA.
- Expedientes de auditoría por granja.
- Matriz documental ICA por expediente.
- Asociación de formatos, documentos, certificaciones y evidencias.
- Registro de hallazgos.
- Clasificación de hallazgos por criticidad.
- Planes de acción correctiva.
- Seguimiento de responsables y fechas compromiso.
- Generación de paquete documental ICA.
- Exportación CSV del expediente.
- Checklist de preparación de visita/auditoría.
- Trazabilidad de entregables.
- Estados de expediente.
- Script para generar paquete documental.
- Migración complementaria de Fase 21.
- Documento técnico de Fase 21.

## Requisitos previos

Debe estar instalada la Fase 20.

## Instalación

1. Instalar fases 01 a 20.
2. Ejecutar:
   `database/migrations/021_auditoria_ica_fase_21.sql`
3. Entrar al menú:
   `Auditoría ICA`
4. Generar paquetes desde consola:
   `php scripts/ica/generate_ica_package.php`

## Nota legal y operativa

Este módulo ayuda a organizar información para auditoría y revisión interna. La entrega oficial ante el ICA debe ser revisada y aprobada por los responsables técnicos, sanitarios y legales de Kriamos.
