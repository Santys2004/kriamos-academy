USE kriamos_biogestion_ica;

-- Permisos complementarios para administración operativa.
INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('granjas','detalle','granjas.detalle','Ver detalle operativo de una granja.'),
('galpones','editar','galpones.editar','Editar galpones.'),
('ciclos','editar','ciclos.editar','Editar ciclos productivos.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.detalle','galpones.editar','ciclos.editar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.detalle','galpones.editar','ciclos.editar')
WHERE r.nombre = 'Administrador de granja';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('granjas.detalle')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Auditor interno');

-- Índices complementarios para consultas operativas.
CREATE INDEX IF NOT EXISTS idx_galpones_estado ON galpones (estado);
CREATE INDEX IF NOT EXISTS idx_ciclos_estado ON ciclos_productivos (estado);
CREATE INDEX IF NOT EXISTS idx_ciclos_fase ON ciclos_productivos (fase_ciclo_id);

-- Asegurar que las granjas iniciales tengan código interno.
UPDATE granjas SET codigo_interno = 'KRI-GALM' WHERE sigla = 'GALM' AND (codigo_interno IS NULL OR codigo_interno = '');
UPDATE granjas SET codigo_interno = 'KRI-GAEE' WHERE sigla = 'GAEE' AND (codigo_interno IS NULL OR codigo_interno = '');
