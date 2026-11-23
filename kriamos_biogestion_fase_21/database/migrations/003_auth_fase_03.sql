USE kriamos_biogestion_ica;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    reason VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX idx_login_attempts_email ON login_attempts (email);
CREATE INDEX idx_login_attempts_ip ON login_attempts (ip);
CREATE INDEX idx_login_attempts_created ON login_attempts (created_at);

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS remember_token VARCHAR(120) NULL AFTER debe_cambiar_password,
    ADD COLUMN IF NOT EXISTS password_changed_at DATETIME NULL AFTER remember_token;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('seguridad','ver','seguridad.ver','Consultar seguridad del sistema.'),
('seguridad','gestionar','seguridad.gestionar','Gestionar políticas de seguridad.'),
('usuarios','bloquear','usuarios.bloquear','Bloquear o desbloquear usuarios.'),
('usuarios','reset_password','usuarios.reset_password','Restablecer contraseña de usuarios.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('seguridad.ver','seguridad.gestionar','usuarios.bloquear','usuarios.reset_password')
WHERE r.nombre = 'Administrador del sistema';
