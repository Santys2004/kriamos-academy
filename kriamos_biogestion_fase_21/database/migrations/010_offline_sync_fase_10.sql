USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('sincronizacion','ver','sincronizacion.ver','Consultar módulo de sincronización offline.'),
('sincronizacion','gestionar','sincronizacion.gestionar','Gestionar eventos de sincronización offline.'),
('sincronizacion','resolver_conflictos','sincronizacion.resolver_conflictos','Resolver conflictos de sincronización.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('sincronizacion.ver','sincronizacion.gestionar','sincronizacion.resolver_conflictos')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('sincronizacion.ver','sincronizacion.gestionar')
WHERE r.nombre IN ('Administrador de granja','Médico veterinario / Responsable sanitario');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('sincronizacion.ver')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

CREATE TABLE IF NOT EXISTS offline_sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    local_id VARCHAR(120) NOT NULL,
    tipo VARCHAR(80) NOT NULL,
    estado ENUM('pendiente','sincronizado','error','conflicto') NOT NULL DEFAULT 'pendiente',
    remote_id BIGINT UNSIGNED NULL,
    payload_json JSON NULL,
    mensaje TEXT NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_offline_sync_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS offline_conflictos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sync_log_id BIGINT UNSIGNED NOT NULL,
    tipo_conflicto VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    payload_local JSON NULL,
    payload_servidor JSON NULL,
    estado ENUM('abierto','resuelto','descartado') NOT NULL DEFAULT 'abierto',
    resuelto_por BIGINT UNSIGNED NULL,
    resuelto_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_offline_conflictos_log FOREIGN KEY (sync_log_id) REFERENCES offline_sync_logs(id),
    CONSTRAINT fk_offline_conflictos_usuario FOREIGN KEY (resuelto_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_offline_sync_usuario ON offline_sync_logs (usuario_id);
CREATE INDEX IF NOT EXISTS idx_offline_sync_estado ON offline_sync_logs (estado);
CREATE INDEX IF NOT EXISTS idx_offline_sync_local_id ON offline_sync_logs (local_id);
CREATE INDEX IF NOT EXISTS idx_offline_conflictos_estado ON offline_conflictos (estado);
