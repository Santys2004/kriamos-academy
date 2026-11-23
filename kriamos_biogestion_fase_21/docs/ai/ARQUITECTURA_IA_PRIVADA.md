# Arquitectura IA privada/documental

## Flujo local

1. Usuario pregunta en `/ia`.
2. El sistema valida permisos.
3. Se filtra la pregunta por riesgo de datos sensibles.
4. Se buscan contextos en `ai_contextos`.
5. Se construye una respuesta local basada en resúmenes/documentos.
6. Se registra la consulta en `ai_consultas`.

## Flujo externo preparado

1. Requiere `ai_external_enabled = 1`.
2. Requiere proveedor y API key.
3. Debe aplicar sanitización de contexto.
4. Debe registrar trazabilidad.
5. Debe respetar política de privacidad de Kriamos.

## Recomendación

Para IA real con documentos, implementar RAG privado con embeddings en base vectorial local o servicio cloud con acuerdo empresarial.
