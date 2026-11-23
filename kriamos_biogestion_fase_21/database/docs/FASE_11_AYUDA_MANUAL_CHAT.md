# Fase 11 - Chat de ayuda y manual interactivo

## Componentes

- Centro de ayuda.
- Artículos por módulo.
- Manual paso a paso.
- Chat interno.
- Base de conocimiento local.
- Registro de preguntas.
- Calificación de utilidad.

## Chat interno

El chat no utiliza IA externa. Busca coincidencias en la tabla `chat_base_conocimiento`.

## Rutas

- `/ayuda`
- `/ayuda/articulo`
- `/ayuda/manual`
- `/ayuda/chat`
- `/ayuda/chat/ask`

## Recomendación futura

Para IA generativa real se recomienda integrar API externa con:
- Protección de datos.
- Control por roles.
- Historial de consultas.
- Límites de uso.
- Revisión de respuestas.
- Base documental propia como contexto.
