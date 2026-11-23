USE kriamos_biogestion_ica;

-- Permisos complementarios para alertas.
INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('alertas','generar','alertas.generar','Generar alertas del sistema.'),
('alertas','gestionar','alertas.gestionar','Cambiar estados y cerrar alertas.'),
('alertas','enviar','alertas.enviar','Enviar notificaciones de alertas por correo.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('alertas.generar','alertas.gestionar','alertas.enviar')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('alertas.gestionar')
WHERE r.nombre IN ('Médico veterinario / Responsable sanitario','Administrador de granja');

-- Tabla de configuración de reglas de alerta.
CREATE TABLE IF NOT EXISTS alert_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    dias_umbral INT NULL,
    prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de historial de notificaciones enviadas.
CREATE TABLE IF NOT EXISTS notificaciones_alerta (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alerta_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    canal ENUM('app','email','push') NOT NULL DEFAULT 'email',
    destinatario VARCHAR(180) NULL,
    asunto VARCHAR(255) NULL,
    estado ENUM('pendiente','enviada','fallida') NOT NULL DEFAULT 'pendiente',
    respuesta TEXT NULL,
    enviada_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificaciones_alerta FOREIGN KEY (alerta_id) REFERENCES alertas(id),
    CONSTRAINT fk_notificaciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_alertas_tipo ON alertas (tipo);
CREATE INDEX IF NOT EXISTS idx_alertas_fecha_limite ON alertas (fecha_limite);
CREATE INDEX IF NOT EXISTS idx_alertas_usuario_estado ON alertas (usuario_id, estado);
CREATE INDEX IF NOT EXISTS idx_notificaciones_alerta ON notificaciones_alerta (alerta_id);

INSERT IGNORE INTO alert_rules (codigo, nombre, descripcion, dias_umbral, prioridad) VALUES
('FORMATO_ATRASADO_3_DIAS','Formato atrasado máximo 3 días','Genera alerta cuando un formato supera el tiempo máximo de atraso definido.',3,'alta'),
('CERTIFICACION_90_DIAS','Certificación próxima a vencer 90 días','Alerta preventiva de vencimiento de certificaciones ICA.',90,'media'),
('CERTIFICACION_60_DIAS','Certificación próxima a vencer 60 días','Alerta preventiva de vencimiento de certificaciones ICA.',60,'media'),
('CERTIFICACION_30_DIAS','Certificación próxima a vencer 30 días','Alerta alta de vencimiento de certificaciones ICA.',30,'alta'),
('CERTIFICACION_15_DIAS','Certificación próxima a vencer 15 días','Alerta crítica de vencimiento de certificaciones ICA.',15,'critica'),
('ACCION_CORRECTIVA_VENCIDA','Acción correctiva vencida','Genera alerta cuando una acción correctiva vence sin cierre.',0,'alta'),
('PRODUCTO_VENCIDO','Producto vencido','Genera alerta cuando se registra medicamento, biológico o producto vencido.',0,'critica'),
('INCIDENCIA_CALIDAD_CRITICA','Incidencia de calidad crítica','Genera alerta por incidencias críticas o altas.',0,'critica');
