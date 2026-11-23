USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('bi','ver','bi.ver','Consultar tablero gerencial BI.'),
('bi','exportar','bi.exportar','Exportar indicadores BI.'),
('bi','gestionar','bi.gestionar','Generar snapshots históricos BI.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('bi.ver','bi.exportar','bi.gestionar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('bi.ver','bi.exportar','bi.gestionar')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('bi.ver')
WHERE r.nombre IN ('Auditor interno');

CREATE TABLE IF NOT EXISTS bi_snapshots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_snapshot DATE NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    filtros_json JSON NULL,
    indicadores_json JSON NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bi_snapshots_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bi_metas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    descripcion VARCHAR(255) NULL,
    valor_meta DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(30) NOT NULL DEFAULT '%',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_bi_snapshots_fecha ON bi_snapshots (fecha_snapshot);
CREATE INDEX IF NOT EXISTS idx_bi_snapshots_usuario ON bi_snapshots (usuario_id);

INSERT IGNORE INTO bi_metas (codigo, nombre, descripcion, valor_meta, unidad) VALUES
('COMPLECION_DOCUMENTAL','Compleción documental','Meta mínima de formatos cerrados/aprobados.',95,'%'),
('FORMATOS_ATRASADOS','Formatos atrasados','Meta máxima de formatos atrasados permitidos.',0,'registros'),
('INCIDENCIAS_CRITICAS','Incidencias críticas','Meta máxima de incidencias críticas abiertas.',0,'registros'),
('CERTIFICACIONES_VENCIDAS','Certificaciones vencidas','Meta máxima de certificaciones vencidas.',0,'registros'),
('MORTALIDAD_ALERTA','Mortalidad diaria alerta','Umbral de revisión sanitaria diaria.',1,'%');
