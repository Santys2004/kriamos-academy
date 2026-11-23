USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('api','ver','api.ver','Consultar panel API.'),
('api','gestionar','api.gestionar','Gestionar clientes y tokens API.'),
('api','logs','api.logs','Consultar logs de consumo API.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('api.ver','api.gestionar','api.logs')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('api.ver','api.logs')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

CREATE TABLE IF NOT EXISTS api_clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    contacto_email VARCHAR(180) NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    ip_permitidas TEXT NULL,
    creado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_clients_creado_por FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_client_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    token_prefix VARCHAR(20) NOT NULL,
    estado ENUM('activo','revocado') NOT NULL DEFAULT 'activo',
    expires_at DATETIME NULL,
    last_used_at DATETIME NULL,
    revoked_at DATETIME NULL,
    creado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_tokens_client FOREIGN KEY (api_client_id) REFERENCES api_clients(id),
    CONSTRAINT fk_api_tokens_creado_por FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_client_id BIGINT UNSIGNED NULL,
    api_token_id BIGINT UNSIGNED NULL,
    metodo VARCHAR(10) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    status_code INT NOT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    request_hash CHAR(64) NULL,
    response_ms INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_logs_client FOREIGN KEY (api_client_id) REFERENCES api_clients(id),
    CONSTRAINT fk_api_logs_token FOREIGN KEY (api_token_id) REFERENCES api_tokens(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_api_clients_estado ON api_clients (estado);
CREATE INDEX IF NOT EXISTS idx_api_tokens_client_estado ON api_tokens (api_client_id, estado);
CREATE INDEX IF NOT EXISTS idx_api_logs_client_fecha ON api_logs (api_client_id, created_at);
CREATE INDEX IF NOT EXISTS idx_api_logs_status ON api_logs (status_code);
