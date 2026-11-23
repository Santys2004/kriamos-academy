USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('ia','ver','ia.ver','Consultar panel de IA documental.'),
('ia','consultar','ia.consultar','Realizar consultas al asistente IA documental.'),
('ia','contextos','ia.contextos','Consultar contextos indexados para IA.'),
('ia','gestionar','ia.gestionar','Gestionar indexación y configuración IA.'),
('ia','ver_logs','ia.ver_logs','Consultar historial de consultas IA.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('ia.ver','ia.consultar','ia.contextos','ia.gestionar','ia.ver_logs')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('ia.ver','ia.consultar','ia.contextos','ia.ver_logs')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja','Auditor interno');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('ia.ver','ia.consultar')
WHERE r.nombre IN ('Auxiliar operativo');

CREATE TABLE IF NOT EXISTS ai_contextos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('documento','ayuda','formato_tipo','poe','certificacion','otro') NOT NULL DEFAULT 'otro',
    referencia_id BIGINT UNSIGNED NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido_resumen TEXT NULL,
    contenido_texto LONGTEXT NULL,
    palabras_clave TEXT NULL,
    estado ENUM('activo','inactivo','archivado') NOT NULL DEFAULT 'activo',
    sensibilidad ENUM('publica','interna','confidencial','restringida') NOT NULL DEFAULT 'interna',
    creado_por BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_contextos_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_consultas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    pregunta TEXT NOT NULL,
    respuesta LONGTEXT NULL,
    modo ENUM('local','externo','bloqueado') NOT NULL DEFAULT 'local',
    proveedor VARCHAR(80) NULL,
    contexto_ids_json JSON NULL,
    tokens_estimados INT UNSIGNED NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_consultas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_prompt_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(180) NOT NULL,
    prompt_sistema TEXT NOT NULL,
    prompt_usuario TEXT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_ai_contextos_tipo ON ai_contextos (tipo);
CREATE INDEX IF NOT EXISTS idx_ai_contextos_estado ON ai_contextos (estado);
CREATE INDEX IF NOT EXISTS idx_ai_consultas_usuario ON ai_consultas (usuario_id);
CREATE INDEX IF NOT EXISTS idx_ai_consultas_fecha ON ai_consultas (created_at);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('ia','ai_external_enabled','0','booleano','Activa o desactiva proveedor externo IA. Por defecto debe estar apagado.',1),
('ia','ai_provider','local','texto','Proveedor IA configurado. local/openai/azure/otro.',1),
('ia','ai_model','local-rules','texto','Modelo o modo de respuesta configurado.',1),
('ia','ai_api_key','','texto','Clave API del proveedor externo. Proteger en producción.',1),
('ia','ai_max_contextos','6','numero','Cantidad máxima de contextos por consulta.',1),
('ia','ai_privacy_mode','strict','texto','Modo de privacidad: strict, standard.',1);

INSERT IGNORE INTO ai_prompt_templates (codigo, nombre, prompt_sistema, prompt_usuario) VALUES
('asistente_documental_kriamos','Asistente documental Kriamos',
'Actúa como asistente documental interno de Kriamos BioGestión ICA. Responde con criterio técnico, profesional, sin inventar información y basado únicamente en el contexto suministrado. Si no hay soporte suficiente, indícalo claramente.',
'Pregunta del usuario: {{pregunta}}. Contexto disponible: {{contexto}}'),
('revision_formato_ica','Revisión de formato ICA',
'Revisa información documental para cumplimiento interno de formatos exigidos por la Resolución ICA aplicable. No emitas conceptos legales definitivos; recomienda validación por responsable técnico.',
'Formato o consulta: {{pregunta}}. Contexto: {{contexto}}');
