# Fase 06 - Firma digital, bloqueo y validaciones

## Firma digital interna

La firma digital interna registra:

- Usuario autenticado.
- Contraseña de confirmación.
- Firma dibujada en pantalla.
- IP.
- Dispositivo.
- Fecha y hora.
- Hash de firma.
- Ruta de archivo PNG cuando se dibuja firma.

## Control de calidad implementado

- Detección de duplicados por tipo de formato, granja, galpón, ciclo y fecha.
- Validación de fechas futuras.
- Validación de números negativos.
- Advertencia de productos vencidos.
- Validación sanitaria básica de mortalidad.
- Revisión básica de posibles errores de digitación.
- Registro de incidencias de calidad.

## Bloqueo de copiar y pegar

El bloqueo aplica en campos marcados con:

`data-no-copy-paste="true"`

Esto incluye observaciones, hallazgos, acciones correctivas, declaración sanitaria y justificación de No aplica.

## Consideración legal

La firma de esta fase es una firma digital interna para trazabilidad empresarial. Para firma digital certificada o firma electrónica con efectos probatorios avanzados, se debe validar el alcance jurídico aplicable en Colombia.
