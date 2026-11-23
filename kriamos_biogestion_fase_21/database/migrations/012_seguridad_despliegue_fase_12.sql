USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('despliegue','ver','despliegue.ver','Consultar checklist de despliegue.'),
('despliegue','gestionar','despliegue.gestionar','Gestionar parámetros de despliegue.'),
('seguridad','eventos','seguridad.eventos','Consultar eventos de seguridad.'),
('seguridad','health','seguridad.health','Consultar estado de salud del sistema.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('despliegue.ver','despliegue.gestionar','seguridad.eventos','seguridad.health')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('despliegue.ver','seguridad.health')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

CREATE TABLE IF NOT EXISTS security_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    tipo VARCHAR(100) NOT NULL,
    nivel ENUM('bajo','medio','alto','critico') NOT NULL DEFAULT 'medio',
    descripcion TEXT NOT NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    contexto_json JSON NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_security_events_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS system_health_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estado ENUM('ok','warning','error') NOT NULL DEFAULT 'ok',
    resultado_json JSON NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS backup_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('database','files','full') NOT NULL,
    estado ENUM('ok','error') NOT NULL,
    archivo_path VARCHAR(255) NULL,
    mensaje TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_security_events_nivel ON security_events (nivel);
CREATE INDEX IF NOT EXISTS idx_security_events_tipo ON security_events (tipo);
CREATE INDEX IF NOT EXISTS idx_security_events_fecha ON security_events (created_at);
CREATE INDEX IF NOT EXISTS idx_health_logs_fecha ON system_health_logs (created_at);
CREATE INDEX IF NOT EXISTS idx_backup_logs_fecha ON backup_logs (created_at);

INSERT INTO security_events (tipo, nivel, descripcion, contexto_json)
VALUES ('fase_12_instalada', 'bajo', 'Migración de seguridad, pruebas y despliegue instalada.', JSON_OBJECT('fase','12'))
ON DUPLICATE KEY UPDATE descripcion = descripcion;
