USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('mobile','ver','mobile.ver','Consultar panel de app móvil.'),
('mobile','gestionar','mobile.gestionar','Gestionar dispositivos y configuración móvil.'),
('mobile','sincronizar','mobile.sincronizar','Sincronizar registros desde app móvil.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('mobile.ver','mobile.gestionar','mobile.sincronizar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('mobile.ver','mobile.sincronizar')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja','Auditor interno','Auxiliar operativo');

CREATE TABLE IF NOT EXISTS mobile_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    platform ENUM('android','ios','web','unknown') NOT NULL DEFAULT 'unknown',
    device_uuid VARCHAR(180) NOT NULL,
    device_name VARCHAR(180) NULL,
    app_version VARCHAR(40) NULL,
    push_token TEXT NULL,
    estado ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
    last_seen_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mobile_device_uuid (device_uuid),
    CONSTRAINT fk_mobile_devices_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mobile_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    token_prefix VARCHAR(20) NOT NULL,
    estado ENUM('activo','revocado','vencido') NOT NULL DEFAULT 'activo',
    expires_at DATETIME NOT NULL,
    last_used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mobile_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mobile_sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    device_id BIGINT UNSIGNED NULL,
    tipo VARCHAR(80) NOT NULL,
    local_id VARCHAR(120) NULL,
    remote_id BIGINT UNSIGNED NULL,
    estado ENUM('recibido','sincronizado','error','conflicto') NOT NULL DEFAULT 'recibido',
    payload_json JSON NULL,
    mensaje TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mobile_sync_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_mobile_sync_device FOREIGN KEY (device_id) REFERENCES mobile_devices(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_mobile_devices_usuario ON mobile_devices (usuario_id);
CREATE INDEX IF NOT EXISTS idx_mobile_tokens_usuario_estado ON mobile_tokens (usuario_id, estado);
CREATE INDEX IF NOT EXISTS idx_mobile_sync_usuario ON mobile_sync_logs (usuario_id);
CREATE INDEX IF NOT EXISTS idx_mobile_sync_estado ON mobile_sync_logs (estado);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('mobile','mobile_api_enabled','1','booleano','Activa endpoints de app móvil.',1),
('mobile','mobile_token_days','30','numero','Días de vigencia del token móvil.',1),
('mobile','mobile_min_app_version','1.0.0','texto','Versión mínima permitida de la app móvil.',1),
('mobile','mobile_offline_enabled','1','booleano','Permite modo offline móvil.',1),
('mobile','mobile_push_enabled','0','booleano','Activa push móvil cuando esté configurado.',1);
