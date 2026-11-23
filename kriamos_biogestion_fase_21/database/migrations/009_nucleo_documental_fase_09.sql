USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('documentos','editar','documentos.editar','Editar metadatos y cargar nuevas versiones documentales.'),
('documentos','descargar','documentos.descargar','Descargar documentos del núcleo documental.'),
('documentos','versionar','documentos.versionar','Crear nuevas versiones de documentos.'),
('evidencias','crear','evidencias.crear','Cargar evidencias documentales.'),
('evidencias','ver','evidencias.ver','Consultar evidencias documentales.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('documentos.editar','documentos.descargar','documentos.versionar','evidencias.crear','evidencias.ver')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('documentos.editar','documentos.descargar','documentos.versionar','evidencias.crear','evidencias.ver')
WHERE r.nombre IN ('Administrador de granja','Médico veterinario / Responsable sanitario');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('documentos.descargar','evidencias.ver')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno','Auxiliar operativo');

CREATE TABLE IF NOT EXISTS documento_versiones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documento_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    version INT UNSIGNED NOT NULL,
    archivo_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NULL,
    extension VARCHAR(20) NULL,
    peso_bytes BIGINT UNSIGNED NULL,
    comentario TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_versiones_documentos FOREIGN KEY (documento_id) REFERENCES documentos(id),
    CONSTRAINT fk_doc_versiones_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documento_etiquetas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    documento_id BIGINT UNSIGNED NOT NULL,
    etiqueta VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_etiquetas_documentos FOREIGN KEY (documento_id) REFERENCES documentos(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_documentos_estado ON documentos (estado);
CREATE INDEX IF NOT EXISTS idx_documentos_granja ON documentos (granja_id);
CREATE INDEX IF NOT EXISTS idx_documentos_extension ON documentos (extension);
CREATE INDEX IF NOT EXISTS idx_documento_versiones_doc ON documento_versiones (documento_id);
CREATE INDEX IF NOT EXISTS idx_evidencias_documento ON evidencias (documento_id);
CREATE INDEX IF NOT EXISTS idx_evidencias_formato ON evidencias (formato_registro_id);

INSERT IGNORE INTO categorias_documentales (codigo, nombre, descripcion) VALUES
('REGISTROS_ICA','Registros ICA','Registros documentales asociados a cumplimiento ICA.'),
('EVIDENCIAS_CAMPO','Evidencias de campo','Fotografías, videos y soportes tomados en granja.'),
('PLANES_MEJORA','Planes de mejora','Planes de acción, seguimiento y cierre de hallazgos.'),
('ACTAS_VISITA','Actas de visita','Actas internas, externas o de autoridad sanitaria.'),
('DOCUMENTOS_LEGALES','Documentos legales','Documentación legal y administrativa relacionada con granjas.');
