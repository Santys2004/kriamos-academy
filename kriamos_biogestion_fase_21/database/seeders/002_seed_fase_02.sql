USE kriamos_biogestion_ica;

INSERT INTO roles (nombre, descripcion) VALUES
('Administrador del sistema', 'Control total de usuarios, granjas, formatos, permisos y configuración.'),
('Gerencia / Dirección', 'Consulta de indicadores, reportes, alertas y cumplimiento general.'),
('Médico veterinario / Responsable sanitario', 'Validación sanitaria, aprobación y firma de registros críticos.'),
('Administrador de granja', 'Diligenciamiento, seguimiento y control documental de la granja asignada.'),
('Auxiliar operativo', 'Diligenciamiento de formatos asignados y carga de evidencias.'),
('Auditor interno', 'Consulta, revisión, auditoría y exportación documental.');

INSERT INTO permisos (modulo, accion, codigo, descripcion) VALUES
('usuarios','ver','usuarios.ver','Consultar usuarios.'),
('usuarios','crear','usuarios.crear','Crear usuarios.'),
('usuarios','editar','usuarios.editar','Editar usuarios.'),
('granjas','ver','granjas.ver','Consultar granjas.'),
('granjas','crear','granjas.crear','Crear granjas.'),
('granjas','editar','granjas.editar','Editar granjas.'),
('galpones','ver','galpones.ver','Consultar galpones.'),
('galpones','crear','galpones.crear','Crear galpones.'),
('ciclos','ver','ciclos.ver','Consultar ciclos productivos.'),
('ciclos','crear','ciclos.crear','Crear ciclos productivos.'),
('formatos','ver','formatos.ver','Consultar formatos.'),
('formatos','crear','formatos.crear','Diligenciar formatos.'),
('formatos','aprobar','formatos.aprobar','Aprobar formatos.'),
('formatos','imprimir','formatos.imprimir','Imprimir formatos.'),
('documentos','ver','documentos.ver','Consultar documentos.'),
('documentos','crear','documentos.crear','Subir documentos.'),
('alertas','ver','alertas.ver','Consultar alertas.'),
('reportes','ver','reportes.ver','Consultar reportes.'),
('auditoria','ver','auditoria.ver','Consultar auditoría.');

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p
WHERE r.nombre = 'Administrador del sistema';

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.ver','galpones.ver','ciclos.ver','formatos.ver','documentos.ver','alertas.ver','reportes.ver')
WHERE r.nombre = 'Gerencia / Dirección';

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.ver','galpones.ver','ciclos.ver','formatos.ver','formatos.crear','formatos.aprobar','formatos.imprimir','documentos.ver','documentos.crear','alertas.ver','reportes.ver')
WHERE r.nombre = 'Médico veterinario / Responsable sanitario';

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.ver','galpones.ver','ciclos.ver','ciclos.crear','formatos.ver','formatos.crear','formatos.imprimir','documentos.ver','documentos.crear','alertas.ver','reportes.ver')
WHERE r.nombre = 'Administrador de granja';

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.ver','galpones.ver','ciclos.ver','formatos.ver','formatos.crear','documentos.ver','alertas.ver')
WHERE r.nombre = 'Auxiliar operativo';

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.ver','galpones.ver','ciclos.ver','formatos.ver','formatos.imprimir','documentos.ver','alertas.ver','reportes.ver','auditoria.ver')
WHERE r.nombre = 'Auditor interno';

INSERT INTO fases_ciclo (codigo, nombre, descripcion, orden) VALUES
('PREPARACION','Preparación','Alistamiento del galpón antes de recibir aves.',1),
('RECEPCION','Recepción','Ingreso y recepción de pollitos.',2),
('LEVANTE','Levante','Etapa inicial de crecimiento.',3),
('ENGORDE','Engorde','Etapa productiva de engorde.',4),
('ALISTAMIENTO','Alistamiento','Preparación para salida.',5),
('SALIDA','Salida','Salida del lote.',6),
('VACIO_SANITARIO','Vacío sanitario','Periodo sanitario posterior a la salida.',7),
('CERRADO','Cerrado','Ciclo cerrado documentalmente.',8);

INSERT INTO estados_documentales (codigo, nombre, descripcion, color) VALUES
('BORRADOR','Borrador','Registro iniciado no finalizado.','gris'),
('PENDIENTE_FIRMA','Pendiente por firma','Registro diligenciado sin firma.','amarillo'),
('DILIGENCIADO','Diligenciado','Registro finalizado por el usuario.','azul'),
('EN_REVISION','En revisión','Registro en revisión sanitaria o documental.','azul'),
('OBSERVADO','Observado','Registro con observaciones.','naranja'),
('CORREGIDO','Corregido','Registro ajustado después de observación.','azul'),
('APROBADO','Aprobado','Registro aprobado.','verde'),
('CERRADO','Cerrado','Registro cerrado y bloqueado.','verde'),
('NO_APLICA','No aplica','Registro marcado como no aplicable con justificación.','gris'),
('VENCIDO','Vencido','Registro fuera de plazo.','rojo'),
('SINCRONIZACION_PENDIENTE','Sincronización pendiente','Registro creado offline pendiente de sincronización.','naranja');

INSERT INTO categorias_documentales (codigo, nombre, descripcion) VALUES
('RESOLUCIONES_ICA','Resoluciones ICA','Normatividad aplicable expedida por el ICA.'),
('CERTIFICACIONES','Certificaciones','Certificados de granja y soportes relacionados.'),
('POE_KRIAMOS','POE Kriamos','Procedimientos operativos estandarizados internos.'),
('MANUALES','Manuales','Manuales técnicos y de usuario.'),
('FICHAS_TECNICAS','Fichas técnicas','Fichas técnicas de productos e insumos.'),
('HOJAS_SEGURIDAD','Hojas de seguridad','Hojas de seguridad de productos.'),
('AUDITORIAS','Auditorías','Actas, informes y planes de auditoría.'),
('EVIDENCIAS','Evidencias','Imágenes, videos y soportes de campo.'),
('CAPACITACIONES','Capacitaciones','Material de capacitación y soportes.'),
('OTROS','Otros','Otros documentos relacionados.');

INSERT INTO granjas (nombre, sigla, codigo_interno, capacidad_pollos, numero_galpones, estado) VALUES
('Granja Avícola La Masía', 'GALM', 'KRI-GALM', 670000, 14, 'activa'),
('Granja Avícola El Encanto', 'GAEE', 'KRI-GAEE', 561000, 11, 'activa');

INSERT INTO galpones (granja_id, codigo, numero, capacidad_pollos)
SELECT g.id, CONCAT(g.sigla, '-G', n.numero), n.numero,
       CASE WHEN g.sigla = 'GALM' THEN ROUND(670000 / 14) ELSE ROUND(561000 / 11) END
FROM granjas g
JOIN (
    SELECT 1 AS numero UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
    UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
) n
WHERE (g.sigla = 'GALM' AND n.numero <= 14)
   OR (g.sigla = 'GAEE' AND n.numero <= 11);

INSERT INTO tipos_formato (codigo, nombre, descripcion, fundamento, frecuencia, permite_no_aplica, requiere_galpon, requiere_ciclo, requiere_firma, dias_maximo_atraso, orden) VALUES
('ING_PERSONAS_VEHICULOS', 'Ingreso de personas, vehículos y objetos', 'Control de ingreso a la granja, procedencia, motivo, vehículo y medidas de bioseguridad.', 'Resolución ICA 3652 de 2014 - POE ingreso de personas, objetos y vehículos.', 'por_evento', 1, 0, 0, 1, 3, 1),
('DESINFECCION_VEHICULOS', 'Desinfección de vehículos', 'Registro de desinfección de vehículos, producto, concentración, tiempo de contacto y responsable.', 'Resolución ICA 3652 de 2014 - POE desinfección de vehículos.', 'por_evento', 1, 0, 0, 1, 3, 2),
('TRATAMIENTO_AGUA', 'Tratamiento y calidad del agua', 'Control de tratamiento de agua, dosificación, pH, cloro residual y verificación.', 'Resolución ICA 3652 de 2014 - POE tratamiento de agua.', 'diario', 1, 1, 1, 1, 3, 3),
('LIMPIEZA_DESINFECCION', 'Limpieza y desinfección de instalaciones, equipos y utensilios', 'Registro de limpieza, desinfección, productos usados, áreas intervenidas y responsable.', 'Resolución ICA 3652 de 2014 - POE limpieza y desinfección.', 'por_evento', 1, 1, 1, 1, 3, 4),
('CONTROL_PLAGAS', 'Control integrado de plagas', 'Registro de monitoreo, hallazgos, productos, ubicación de controles y acciones correctivas.', 'Resolución ICA 3652 de 2014 - POE control integrado de plagas.', 'semanal', 1, 0, 0, 1, 3, 5),
('MORTALIDAD_DIARIA', 'Mortalidad diaria', 'Registro diario de mortalidad por galpón, edad, cantidad y causa probable.', 'Resolución ICA 3652 de 2014 - POE manejo de mortalidad.', 'diario', 0, 1, 1, 1, 3, 6),
('DISPOSICION_MORTALIDAD', 'Manejo y disposición de mortalidad', 'Control de recolección, manejo, disposición, hora y responsable.', 'Resolución ICA 3652 de 2014 - POE disposición de mortalidad.', 'diario', 1, 1, 1, 1, 3, 7),
('TRATAMIENTO_POLLINAZA', 'Tratamiento térmico de pollinaza', 'Registro de temperatura, tiempo, resultado y responsable del tratamiento.', 'Resolución ICA 3652 de 2014 - POE tratamiento de pollinaza.', 'por_ciclo', 1, 1, 1, 1, 3, 8),
('RESIDUOS', 'Manejo de residuos sólidos y líquidos', 'Control de residuos generados, cantidad, manejo y disposición final.', 'Resolución ICA 3652 de 2014 - POE manejo de residuos.', 'por_evento', 1, 0, 0, 1, 3, 9),
('VACUNACION', 'Programa sanitario y vacunación', 'Registro de vacuna, laboratorio, lote, vencimiento, dosis, vía y responsable sanitario.', 'Resolución ICA 3652 de 2014 - Programa de vacunación obligatorio.', 'por_evento', 1, 1, 1, 1, 3, 10),
('MEDICAMENTOS_BIOLOGICOS', 'Uso de medicamentos, biológicos y plaguicidas', 'Registro de producto, principio activo, lote, dosis, motivo, tiempo de retiro y responsable.', 'Resolución ICA 3652 de 2014 - Buenas prácticas en uso de insumos veterinarios.', 'por_evento', 1, 1, 1, 1, 3, 11),
('CAPACITACION', 'Capacitación del personal', 'Registro de capacitación, tema, asistentes, duración, evidencia y evaluación.', 'Resolución ICA 3652 de 2014 - Capacitación del personal.', 'por_evento', 1, 0, 0, 1, 3, 12),
('MANTENIMIENTO', 'Mantenimiento preventivo y correctivo', 'Control de mantenimiento de instalaciones, equipos y utensilios.', 'Resolución ICA 3652 de 2014 - Mantenimiento preventivo y correctivo.', 'por_evento', 1, 1, 0, 1, 3, 13),
('HALLAZGOS_ACCIONES', 'Observaciones, hallazgos y acciones correctivas', 'Gestión de hallazgos, criticidad, acciones, responsables, fecha límite y cierre.', 'POE Kriamos - Gestión de hallazgos y acciones correctivas.', 'por_evento', 1, 0, 0, 1, 3, 14);

-- Usuario demo para pruebas posteriores. La contraseña debe cambiarse en Fase 03.
INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, cargo)
SELECT id, 'Administrador', 'Kriamos', 'admin@kriamos.com.co', NULL, 'Administrador del sistema'
FROM roles
WHERE nombre = 'Administrador del sistema'
LIMIT 1;

-- Certificaciones iniciales de ejemplo. Ajustar fechas y soportes reales cuando se cargue información oficial.
INSERT INTO certificaciones_granja (granja_id, numero_certificacion, entidad, fecha_expedicion, fecha_vencimiento, estado, responsable_id, observaciones)
SELECT g.id, NULL, 'ICA', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'vigente', u.id, 'Registro inicial pendiente de cargar soporte oficial.'
FROM granjas g
LEFT JOIN usuarios u ON u.email = 'admin@kriamos.com.co';
