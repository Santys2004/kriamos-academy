USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('firma_avanzada','ver','firma_avanzada.ver','Consultar módulo de firma electrónica avanzada.'),
('firma_avanzada','firmar','firma_avanzada.firmar','Realizar firma avanzada de formatos y documentos.'),
('firma_avanzada','verificar','firma_avanzada.verificar','Verificar integridad de firmas avanzadas.'),
('firma_avanzada','certificados','firma_avanzada.certificados','Consultar certificados de firma.'),
('firma_avanzada','gestionar','firma_avanzada.gestionar','Gestionar certificados y parámetros de firma avanzada.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('firma_avanzada.ver','firma_avanzada.firmar','firma_avanzada.verificar','firma_avanzada.certificados','firma_avanzada.gestionar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('firma_avanzada.ver','firma_avanzada.firmar','firma_avanzada.verificar','firma_avanzada.certificados')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja','Auditor interno');

CREATE TABLE IF NOT EXISTS certificados_firma (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo ENUM('interno','externo') NOT NULL DEFAULT 'interno',
    serial VARCHAR(120) NOT NULL UNIQUE,
    huella_sha256 CHAR(64) NOT NULL,
    entidad_emisora VARCHAR(180) NOT NULL,
    vigente_desde DATE NULL,
    vigente_hasta DATE NULL,
    estado ENUM('activo','revocado','vencido') NOT NULL DEFAULT 'activo',
    metadata_json JSON NULL,
    revocado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificados_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS firmas_avanzadas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    certificado_id BIGINT UNSIGNED NOT NULL,
    formato_registro_id BIGINT UNSIGNED NULL,
    documento_id BIGINT UNSIGNED NULL,
    sello_tiempo_id BIGINT UNSIGNED NULL,
    tipo_objeto ENUM('formato','documento','otro') NOT NULL,
    objeto_id BIGINT UNSIGNED NOT NULL,
    hash_original CHAR(64) NOT NULL,
    hash_firma CHAR(64) NOT NULL,
    evidencia_json JSON NOT NULL,
    estado ENUM('firmada','sellada','revocada','error') NOT NULL DEFAULT 'firmada',
    estado_verificacion ENUM('sin_verificar','valida','alterada','sin_sello','error') NOT NULL DEFAULT 'sin_verificar',
    mensaje_verificacion TEXT NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    sellada_at DATETIME NULL,
    verificada_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_firmas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_firmas_certificado FOREIGN KEY (certificado_id) REFERENCES certificados_firma(id),
    CONSTRAINT fk_firmas_formato FOREIGN KEY (formato_registro_id) REFERENCES formatos_registros(id),
    CONSTRAINT fk_firmas_documento FOREIGN KEY (documento_id) REFERENCES documentos(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sellos_tiempo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firma_avanzada_id BIGINT UNSIGNED NOT NULL,
    proveedor VARCHAR(120) NOT NULL,
    timestamp_utc DATETIME NOT NULL,
    hash_sellado CHAR(64) NOT NULL,
    token_sello TEXT NOT NULL,
    respuesta_json JSON NULL,
    estado ENUM('emitido','error','revocado') NOT NULL DEFAULT 'emitido',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sellos_firma FOREIGN KEY (firma_avanzada_id) REFERENCES firmas_avanzadas(id)
) ENGINE=InnoDB;

ALTER TABLE firmas_avanzadas
ADD CONSTRAINT fk_firmas_sello
FOREIGN KEY (sello_tiempo_id) REFERENCES sellos_tiempo(id);

CREATE TABLE IF NOT EXISTS otp_firma (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    codigo_hash CHAR(64) NOT NULL,
    estado ENUM('pendiente','usado','vencido') NOT NULL DEFAULT 'pendiente',
    expires_at DATETIME NOT NULL,
    usado_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_otp_firma_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_certificados_usuario_estado ON certificados_firma (usuario_id, estado);
CREATE INDEX IF NOT EXISTS idx_firmas_usuario ON firmas_avanzadas (usuario_id);
CREATE INDEX IF NOT EXISTS idx_firmas_objeto ON firmas_avanzadas (tipo_objeto, objeto_id);
CREATE INDEX IF NOT EXISTS idx_firmas_estado ON firmas_avanzadas (estado);
CREATE INDEX IF NOT EXISTS idx_sellos_firma ON sellos_tiempo (firma_avanzada_id);
CREATE INDEX IF NOT EXISTS idx_otp_usuario_estado ON otp_firma (usuario_id, estado);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('firma_avanzada','firma_avanzada_enabled','1','booleano','Activa firma electrónica avanzada interna.',1),
('firma_avanzada','otp_firma_minutos','10','numero','Minutos de vigencia del OTP de firma.',1),
('firma_avanzada','tsa_provider','KRIAMOS_INTERNAL_TSA','texto','Proveedor de sellado de tiempo configurado.',1),
('firma_avanzada','tsa_external_enabled','0','booleano','Activa TSA externa cuando se configure proveedor autorizado.',1),
('firma_avanzada','firma_require_password','1','booleano','Requiere validación reforzada de identidad para firmar.',1),
('firma_avanzada','algoritmo_hash','SHA-256','texto','Algoritmo de hash documental.',0);
