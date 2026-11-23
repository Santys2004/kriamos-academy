USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('notificaciones','ver','notificaciones.ver','Consultar centro de notificaciones.'),
('notificaciones','gestionar','notificaciones.gestionar','Gestionar cola de correos y notificaciones.'),
('notificaciones','push','notificaciones.push','Gestionar suscripciones push PWA.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('notificaciones.ver','notificaciones.gestionar','notificaciones.push')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('notificaciones.ver','notificaciones.push')
WHERE r.nombre IN ('Administrador de granja','Médico veterinario / Responsable sanitario','Gerencia / Dirección','Auditor interno','Auxiliar operativo');

CREATE TABLE IF NOT EXISTS notificaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    alerta_id BIGINT UNSIGNED NULL,
    canal ENUM('app','email','push') NOT NULL DEFAULT 'app',
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    estado ENUM('pendiente','enviada','leida','error') NOT NULL DEFAULT 'pendiente',
    url_accion VARCHAR(255) NULL,
    leida_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificaciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_notificaciones_alerta FOREIGN KEY (alerta_id) REFERENCES alertas(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mail_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    alerta_id BIGINT UNSIGNED NULL,
    notificacion_id BIGINT UNSIGNED NULL,
    destinatario VARCHAR(180) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    cuerpo_html LONGTEXT NOT NULL,
    prioridad ENUM('baja','media','alta','critica') NOT NULL DEFAULT 'media',
    estado ENUM('pendiente','enviado','error','cancelado') NOT NULL DEFAULT 'pendiente',
    intentos INT UNSIGNED NOT NULL DEFAULT 0,
    programado_at DATETIME NULL,
    enviado_at DATETIME NULL,
    respuesta_smtp TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mail_queue_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_mail_queue_alerta FOREIGN KEY (alerta_id) REFERENCES alertas(id),
    CONSTRAINT fk_mail_queue_notificacion FOREIGN KEY (notificacion_id) REFERENCES notificaciones(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mail_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    cuerpo_html LONGTEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS push_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    endpoint VARCHAR(600) NOT NULL,
    p256dh TEXT NULL,
    auth TEXT NULL,
    user_agent VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_push_endpoint (endpoint),
    CONSTRAINT fk_push_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_estado ON notificaciones (usuario_id, estado);
CREATE INDEX IF NOT EXISTS idx_mail_queue_estado ON mail_queue (estado);
CREATE INDEX IF NOT EXISTS idx_mail_queue_prioridad ON mail_queue (prioridad);
CREATE INDEX IF NOT EXISTS idx_push_usuario ON push_subscriptions (usuario_id);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('smtp','smtp_host','smtp.kriamos.com.co','texto','Servidor SMTP corporativo.',1),
('smtp','smtp_port','587','numero','Puerto SMTP. Usualmente 587 TLS o 465 SSL.',1),
('smtp','smtp_username','notificaciones@kriamos.com.co','texto','Usuario SMTP.',1),
('smtp','smtp_password','','texto','Contraseña SMTP. En producción proteger este valor.',1),
('smtp','smtp_from_address','notificaciones@kriamos.com.co','texto','Correo remitente.',1),
('smtp','smtp_from_name','Kriamos BioGestión ICA','texto','Nombre del remitente.',1),
('push','vapid_public_key','','texto','Clave pública VAPID para Web Push.',1),
('push','vapid_private_key','','texto','Clave privada VAPID para Web Push.',1),
('notificaciones','activar_correo','1','booleano','Activar envío de correos.',1),
('notificaciones','activar_push','0','booleano','Activar push real cuando se configure VAPID.',1);

INSERT IGNORE INTO mail_templates (codigo, nombre, asunto, cuerpo_html) VALUES
('alerta_general','Alerta general','[Kriamos BioGestión ICA] {{titulo}}',
'<html><body style="font-family: Arial, sans-serif; color:#1f2a3d;"><div style="border:1px solid #dbe3ef;border-radius:14px;padding:20px;"><h2 style="color:#143560;">{{titulo}}</h2><p>{{mensaje}}</p><p><a href="{{url}}" style="background:#f28c28;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;">Ver en el sistema</a></p><p style="font-size:12px;color:#68758a;">Mensaje automático generado por {{empresa}} BioGestión ICA.</p></div></body></html>');
