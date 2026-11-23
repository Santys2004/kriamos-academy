# Fase 17 - IA privada/documental

## Propósito

Agregar una capa de consulta inteligente sobre la información documental y operativa del sistema, manteniendo la privacidad de la información de Kriamos.

## Modo de operación

Por defecto, el asistente funciona en modo local:
- Busca contexto documental.
- Resume coincidencias.
- No envía datos a proveedores externos.
- Registra consulta y respuesta en auditoría IA.

## Componentes

- `ai_contextos`
- `ai_consultas`
- `ai_prompt_templates`
- Servicio de búsqueda contextual.
- Sanitizador de datos sensibles.
- Servicio de respuesta local.
- Cliente preparado para proveedor externo.

## Rutas

- `/ia`
- `/ia/ask`
- `/ia/contextos`
- `/ia/contextos/indexar`
- `/ia/consultas`
- `/ia/consultas/csv`

## Script

`php scripts/index_ai_context.php`

## Consideraciones de privacidad

No active proveedor externo sin:
1. Revisión jurídica.
2. Autorización interna.
3. Acuerdo de tratamiento de datos.
4. Política de no entrenamiento con datos de Kriamos.
5. Control de acceso por roles.
6. Registro de auditoría.
7. Clasificación de sensibilidad documental.
