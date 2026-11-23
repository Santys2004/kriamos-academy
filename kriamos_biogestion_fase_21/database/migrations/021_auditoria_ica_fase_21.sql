USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('auditoria_ica','ver','auditoria_ica.ver','Consultar módulo de auditoría ICA.'),
('auditoria_ica','gestionar','auditoria_ica.gestionar','Gestionar expedientes, hallazgos y planes ICA.'),
('auditoria_ica','exportar','auditoria_ica.exportar','Generar paquetes y exportaciones de auditoría ICA.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('auditoria_ica.ver','auditoria_ica.gestionar','auditoria_ica.exportar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('auditoria_ica.ver','auditoria_ica.gestionar','auditoria_ica.exportar')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja','Auditor interno');

CREATE TABLE IF NOT EXISTS ica_expedientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    granja_id BIGINT UNSIGNED NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    objetivo TEXT NULL,
    alcance TEXT NULL,
    estado ENUM('borrador','preparacion','en_revision','listo','cerrado','archivado') NOT NULL DEFAULT 'borrador',
    creado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ica_expedientes_granja FOREIGN KEY (granja_id) REFERENCES granjas(id),
    CONSTRAINT fk_ica_expedientes_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ica_matriz_documental (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expediente_id BIGINT UNSIGNED NOT NULL,
    categoria VARCHAR(120) NOT NULL,
    requisito VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    documento_id BIGINT UNSIGNED NULL,
    tipo_formato_id BIGINT UNSIGNED NULL,
    certificacion_id BIGINT UNSIGNED NULL,
    obligatorio TINYINT(1) NOT NULL DEFAULT 1,
    estado_cumplimiento ENUM('pendiente','pendiente_revision','cumple','no_cumple','no_aplica') NOT NULL DEFAULT 'pendiente',
    observacion TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ica_matriz_expediente FOREIGN KEY (expediente_id) REFERENCES ica_expedientes(id),
    CONSTRAINT fk_ica_matriz_documento FOREIGN KEY (documento_id) REFERENCES documentos(id),
    CONSTRAINT fk_ica_matriz_tipo_formato FOREIGN KEY (tipo_formato_id) REFERENCES tipos_formato(id),
    CONSTRAINT fk_ica_matriz_certificacion FOREIGN KEY (certificacion_id) REFERENCES certificaciones_granja(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ica_hallazgos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expediente_id BIGINT UNSIGNED NOT NULL,
    matriz_documental_id BIGINT UNSIGNED NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    criticidad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    estado ENUM('abierto','en_gestion','cerrado','descartado') NOT NULL DEFAULT 'abierto',
    evidencia_path VARCHAR(255) NULL,
    reportado_por BIGINT UNSIGNED NULL,
    cerrado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ica_hallazgos_expediente FOREIGN KEY (expediente_id) REFERENCES ica_expedientes(id),
    CONSTRAINT fk_ica_hallazgos_matriz FOREIGN KEY (matriz_documental_id) REFERENCES ica_matriz_documental(id),
    CONSTRAINT fk_ica_hallazgos_usuario FOREIGN KEY (reportado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ica_planes_accion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expediente_id BIGINT UNSIGNED NOT NULL,
    hallazgo_id BIGINT UNSIGNED NULL,
    accion TEXT NOT NULL,
    responsable_id BIGINT UNSIGNED NULL,
    fecha_compromiso DATE NOT NULL,
    estado ENUM('abierto','en_proceso','cerrado','vencido','cancelado') NOT NULL DEFAULT 'abierto',
    avance_porcentaje INT UNSIGNED NOT NULL DEFAULT 0,
    observaciones TEXT NULL,
    cerrado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ica_planes_expediente FOREIGN KEY (expediente_id) REFERENCES ica_expedientes(id),
    CONSTRAINT fk_ica_planes_hallazgo FOREIGN KEY (hallazgo_id) REFERENCES ica_hallazgos(id),
    CONSTRAINT fk_ica_planes_responsable FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ica_exportaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expediente_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('paquete_zip','matriz_csv','reporte_pdf','otro') NOT NULL DEFAULT 'paquete_zip',
    archivo_path VARCHAR(255) NOT NULL,
    hash_sha256 CHAR(64) NOT NULL,
    total_archivos INT UNSIGNED NOT NULL DEFAULT 0,
    generado_por BIGINT UNSIGNED NULL,
    estado ENUM('generado','entregado','anulado') NOT NULL DEFAULT 'generado',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ica_exportaciones_expediente FOREIGN KEY (expediente_id) REFERENCES ica_expedientes(id),
    CONSTRAINT fk_ica_exportaciones_usuario FOREIGN KEY (generado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_ica_expedientes_granja_estado ON ica_expedientes (granja_id, estado);
CREATE INDEX IF NOT EXISTS idx_ica_matriz_expediente ON ica_matriz_documental (expediente_id);
CREATE INDEX IF NOT EXISTS idx_ica_hallazgos_expediente_estado ON ica_hallazgos (expediente_id, estado);
CREATE INDEX IF NOT EXISTS idx_ica_planes_expediente_estado ON ica_planes_accion (expediente_id, estado);
CREATE INDEX IF NOT EXISTS idx_ica_exportaciones_expediente ON ica_exportaciones (expediente_id);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('auditoria_ica','ica_audit_enabled','1','booleano','Activa módulo de auditoría ICA.',1),
('auditoria_ica','ica_package_path','storage/ica_audit/paquetes','texto','Ruta de paquetes documentales ICA.',1),
('auditoria_ica','ica_plan_warning_days','5','numero','Días previos para alertar vencimiento de planes de acción.',1),
('auditoria_ica','ica_required_review','1','booleano','Exige revisión interna antes de marcar expediente listo.',1);
