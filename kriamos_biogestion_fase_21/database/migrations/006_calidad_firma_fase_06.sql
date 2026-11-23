USE kriamos_biogestion_ica;

-- Permisos complementarios para Fase 06.
INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('calidad','ver','calidad.ver','Consultar panel de control de calidad de información.'),
('calidad','gestionar','calidad.gestionar','Gestionar incidencias de calidad.'),
('firmas','crear','firmas.crear','Crear firma digital interna.'),
('firmas','ver','firmas.ver','Consultar firmas digitales internas.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('calidad.ver','calidad.gestionar','firmas.crear','firmas.ver')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('calidad.ver','firmas.crear','firmas.ver')
WHERE r.nombre IN ('Médico veterinario / Responsable sanitario','Administrador de granja');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('calidad.ver','firmas.ver')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

-- La firma usa el permiso existente formatos.aprobar.
INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo = 'formatos.aprobar'
WHERE r.nombre IN ('Administrador del sistema','Médico veterinario / Responsable sanitario','Administrador de granja');

CREATE TABLE IF NOT EXISTS incidencias_calidad (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    formato_registro_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('duplicado','validacion','ortografia','sanitaria','vencimiento','otro') NOT NULL DEFAULT 'validacion',
    campo VARCHAR(120) NULL,
    mensaje TEXT NOT NULL,
    nivel ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    estado ENUM('abierta','en_revision','cerrada') NOT NULL DEFAULT 'abierta',
    observacion_cierre TEXT NULL,
    cerrado_por BIGINT UNSIGNED NULL,
    cerrado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_incidencias_registros FOREIGN KEY (formato_registro_id) REFERENCES formatos_registros(id),
    CONSTRAINT fk_incidencias_cerrado_por FOREIGN KEY (cerrado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS diccionario_tecnico (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    termino VARCHAR(120) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    categoria VARCHAR(80) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_incidencias_registro ON incidencias_calidad (formato_registro_id);
CREATE INDEX IF NOT EXISTS idx_incidencias_estado ON incidencias_calidad (estado);
CREATE INDEX IF NOT EXISTS idx_firmas_registro ON firmas_digitales (formato_registro_id);

INSERT IGNORE INTO diccionario_tecnico (termino, descripcion, categoria) VALUES
('GALM','Granja Avícola La Masía','granja'),
('GAEE','Granja Avícola El Encanto','granja'),
('bioseguridad','Conjunto de medidas sanitarias preventivas','sanidad'),
('galpón','Infraestructura productiva para alojamiento de aves','producción'),
('encasetamiento','Ingreso de pollitos al galpón','producción'),
('pollinaza','Material resultante de cama avícola y excretas','producción'),
('desinfección','Proceso de reducción de microorganismos','bioseguridad'),
('vacunación','Aplicación de biológico según programa sanitario','sanidad'),
('mortalidad','Número de aves muertas en un periodo','sanidad'),
('ICA','Instituto Colombiano Agropecuario','normativo');
