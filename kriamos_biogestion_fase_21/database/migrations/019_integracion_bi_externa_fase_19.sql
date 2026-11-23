USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('bi_externo','ver','bi_externo.ver','Consultar panel de integración BI externa.'),
('bi_externo','gestionar','bi_externo.gestionar','Gestionar conexiones y tokens BI externos.'),
('bi_externo','exportar','bi_externo.exportar','Exportar datasets BI.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('bi_externo.ver','bi_externo.gestionar','bi_externo.exportar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('bi_externo.ver','bi_externo.exportar')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

CREATE TABLE IF NOT EXISTS bi_external_connections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    herramienta ENUM('power_bi','metabase','looker_studio','superset','otro') NOT NULL DEFAULT 'otro',
    descripcion TEXT NULL,
    contacto_email VARCHAR(180) NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    ip_permitidas TEXT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    token_prefix VARCHAR(20) NOT NULL,
    creado_por BIGINT UNSIGNED NULL,
    last_used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bi_ext_connections_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bi_external_datasets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    vista_sql VARCHAR(120) NOT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    ultimo_archivo_path VARCHAR(255) NULL,
    ultima_cantidad_registros INT UNSIGNED NULL,
    ultimo_generado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bi_external_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    connection_id BIGINT UNSIGNED NULL,
    dataset_codigo VARCHAR(80) NOT NULL,
    formato ENUM('json','csv') NOT NULL DEFAULT 'json',
    status_code INT NOT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    response_rows INT UNSIGNED NOT NULL DEFAULT 0,
    response_ms INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bi_ext_logs_connection FOREIGN KEY (connection_id) REFERENCES bi_external_connections(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_bi_ext_connections_estado ON bi_external_connections (estado);
CREATE INDEX IF NOT EXISTS idx_bi_ext_datasets_codigo ON bi_external_datasets (codigo);
CREATE INDEX IF NOT EXISTS idx_bi_ext_logs_fecha ON bi_external_logs (created_at);
CREATE INDEX IF NOT EXISTS idx_bi_ext_logs_dataset ON bi_external_logs (dataset_codigo);

INSERT IGNORE INTO bi_external_datasets (codigo, nombre, descripcion, vista_sql, estado) VALUES
('granjas','Granjas','Datos maestros de granjas.', 'vw_bi_granjas', 'activo'),
('galpones','Galpones','Datos maestros de galpones.', 'vw_bi_galpones', 'activo'),
('formatos','Formatos diligenciados','Registros documentales diligenciados.', 'vw_bi_formatos', 'activo'),
('alertas','Alertas','Alertas y vencimientos registrados.', 'vw_bi_alertas', 'activo'),
('certificaciones','Certificaciones','Estado de certificaciones por granja.', 'vw_bi_certificaciones', 'activo'),
('documentos','Documentos','Núcleo documental clasificado.', 'vw_bi_documentos', 'activo'),
('indicadores_granja','Indicadores por granja','Cumplimiento documental por granja.', 'vw_bi_indicadores_granja', 'activo'),
('mortalidad_diaria','Mortalidad diaria','Consolidado de mortalidad diaria.', 'vw_bi_mortalidad_diaria', 'activo'),
('uso_sistema','Uso del sistema','Actividad de usuarios y auditoría.', 'vw_bi_uso_sistema', 'activo');

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('bi_externo','bi_external_enabled','1','booleano','Activa consumo BI externo.',1),
('bi_externo','bi_external_max_rows','10000','numero','Máximo de filas por consulta BI externa.',1),
('bi_externo','bi_external_require_ip','0','booleano','Exigir IP autorizada en conexiones BI.',1),
('bi_externo','bi_external_export_path','storage/bi/external_exports','texto','Ruta de exportaciones CSV BI.',1);
