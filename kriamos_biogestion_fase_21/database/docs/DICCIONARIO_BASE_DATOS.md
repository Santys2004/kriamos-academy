# Diccionario técnico de base de datos - Fase 02

## roles
Perfiles base del sistema: administrador, gerencia, médico veterinario, administrador de granja, auxiliar operativo y auditor interno.

## permisos
Acciones disponibles por módulo. Ejemplo: usuarios.ver, granjas.crear, formatos.aprobar.

## rol_permiso
Tabla pivote entre roles y permisos.

## usuarios
Personal propio de Kriamos. La autenticación se implementará en Fase 03.

## granjas
Unidades productivas de Kriamos. Inicialmente:
- GALM: Granja Avícola La Masía.
- GAEE: Granja Avícola El Encanto.

## galpones
Infraestructura productiva asociada a cada granja.

## fases_ciclo
Catálogo de fases productivas:
- Preparación.
- Recepción.
- Levante.
- Engorde.
- Alistamiento.
- Salida.
- Vacío sanitario.
- Cerrado.

## ciclos_productivos
Ciclos por galpón y granja. Permite determinar qué formatos aplican según la fase.

## tipos_formato
Catálogo de formatos ICA 3652 y POE Kriamos.

## formatos_registros
Registros diligenciados. El campo datos_json permite guardar los datos específicos del formato.

## firmas_digitales
Firmas internas asociadas a registros.

## certificaciones_granja
Control de vigencias de certificaciones ICA por granja.

## documentos
Núcleo documental: PDF, Word, Excel, JPG, PNG, MP4, MOV, CSV y otros.

## evidencias
Archivos asociados a formatos, hallazgos, auditorías o documentos.

## alertas
Alertas por formatos atrasados, certificaciones, acciones correctivas o registros pendientes.

## auditoria
Trazabilidad completa de acciones dentro del sistema.
