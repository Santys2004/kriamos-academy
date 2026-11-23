USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('administracion','ver','administracion.ver','Consultar panel de administración avanzada.'),
('roles','gestionar','roles.gestionar','Gestionar roles y permisos.'),
('parametros','gestionar','parametros.gestionar','Gestionar parámetros generales del sistema.'),
('usuarios','gestionar','usuarios.gestionar','Crear y editar usuarios del sistema.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('administracion.ver','roles.gestionar','parametros.gestionar','usuarios.gestionar','usuarios.bloquear','usuarios.reset_password','auditoria.ver')
WHERE r.nombre = 'Administrador del sistema';

CREATE TABLE IF NOT EXISTS parametros_sistema (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grupo VARCHAR(80) NOT NULL,
    clave VARCHAR(120) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    tipo ENUM('texto','numero','booleano','json','fecha') NOT NULL DEFAULT 'texto',
    descripcion VARCHAR(255) NULL,
    editable TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_parametros_grupo ON parametros_sistema (grupo);

INSERT IGNORE INTO parametros_sistema (grupo, clave, valor, tipo, descripcion, editable) VALUES
('seguridad','dominio_corporativo','kriamos.com.co','texto','Dominio corporativo permitido para usuarios internos.',1),
('seguridad','max_intentos_login','5','numero','Cantidad máxima de intentos fallidos antes de bloqueo temporal.',1),
('seguridad','minutos_bloqueo_login','15','numero','Tiempo de bloqueo por intentos fallidos.',1),
('alertas','dias_atraso_formatos','3','numero','Días máximos permitidos para formatos atrasados.',1),
('alertas','dias_certificacion_critica','15','numero','Umbral crítico de vencimiento de certificaciones.',1),
('documentos','tamano_maximo_mb','50','numero','Tamaño máximo de archivo permitido en MB.',1),
('documentos','extensiones_permitidas','pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp,mp4,mov,txt','texto','Extensiones permitidas para carga documental.',1),
('sistema','nombre_empresa','Kriamos','texto','Nombre de la empresa.',1),
('sistema','nombre_aplicativo','Kriamos BioGestión ICA','texto','Nombre oficial del aplicativo.',1),
('sistema','version_actual','13','numero','Versión/fase actual del sistema.',0);
