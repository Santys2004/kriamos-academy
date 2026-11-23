# Fase 05 - Formatos ICA 3652

## Propósito

La Fase 05 desarrolla el módulo inicial para diligenciar formatos digitales exigidos por la Resolución ICA 3652 de 2014 y ajustados al POE de Kriamos.

## Principios funcionales

- Todo formato debe estar asociado a una granja.
- Algunos formatos requieren galpón.
- Algunos formatos requieren ciclo productivo.
- La opción No aplica solo se permite si el tipo de formato lo autoriza.
- Si se marca No aplica, el motivo es obligatorio.
- Cada registro genera consecutivo y hash documental.
- Los datos específicos se almacenan en `datos_json`.
- La firma digital queda para Fase 06.
- La impresión y exportación avanzada quedan para Fase 08.

## Formatos base

1. Ingreso de personas, vehículos y objetos.
2. Desinfección de vehículos.
3. Tratamiento y calidad del agua.
4. Limpieza y desinfección.
5. Control integrado de plagas.
6. Mortalidad diaria.
7. Manejo y disposición de mortalidad.
8. Tratamiento térmico de pollinaza.
9. Manejo de residuos sólidos y líquidos.
10. Programa sanitario y vacunación.
11. Uso de medicamentos, biológicos y plaguicidas.
12. Capacitación del personal.
13. Mantenimiento preventivo y correctivo.
14. Observaciones, hallazgos y acciones correctivas.

## Relación con próximas fases

### Fase 06
Agrega firma digital, bloqueo avanzado de copiar/pegar, validaciones de duplicados, control de errores y corrector ortográfico.

### Fase 07
Usa los registros para generar alertas por atraso máximo de 3 días, vencimientos y escalamiento.

### Fase 08
Permite descargar PDF, Excel, imprimir y generar paquete ICA.
