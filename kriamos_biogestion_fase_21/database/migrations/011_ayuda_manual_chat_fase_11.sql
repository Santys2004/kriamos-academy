USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('ayuda','ver','ayuda.ver','Consultar centro de ayuda, manual interactivo y chat interno.'),
('ayuda','gestionar','ayuda.gestionar','Gestionar artículos, manual y base de conocimiento.'),
('chat','ver_logs','chat.ver_logs','Consultar registros del chat de ayuda.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('ayuda.ver','ayuda.gestionar','chat.ver_logs')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('ayuda.ver')
WHERE r.nombre IN ('Administrador de granja','Médico veterinario / Responsable sanitario','Gerencia / Dirección','Auditor interno','Auxiliar operativo');

CREATE TABLE IF NOT EXISTS ayuda_articulos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(80) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    titulo VARCHAR(180) NOT NULL,
    resumen VARCHAR(255) NULL,
    contenido TEXT NOT NULL,
    palabras_clave TEXT NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS manual_pasos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(80) NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    ruta_sistema VARCHAR(180) NULL,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_base_conocimiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pregunta VARCHAR(255) NOT NULL,
    respuesta TEXT NOT NULL,
    palabras_clave TEXT NULL,
    modulo VARCHAR(80) NULL,
    prioridad INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_ayuda_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    pregunta TEXT NOT NULL,
    respuesta TEXT NULL,
    base_conocimiento_id BIGINT UNSIGNED NULL,
    util TINYINT(1) NULL,
    ip VARCHAR(45) NULL,
    dispositivo VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chat_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_chat_logs_base FOREIGN KEY (base_conocimiento_id) REFERENCES chat_base_conocimiento(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_ayuda_modulo ON ayuda_articulos (modulo);
CREATE INDEX IF NOT EXISTS idx_manual_modulo ON manual_pasos (modulo);
CREATE INDEX IF NOT EXISTS idx_chat_modulo ON chat_base_conocimiento (modulo);

INSERT IGNORE INTO ayuda_articulos (modulo, slug, titulo, resumen, contenido, palabras_clave, orden) VALUES
('Inicio','inicio-sistema','Cómo ingresar al sistema','Ingreso corporativo con correo autorizado.','Para ingresar al sistema debe usar su correo corporativo autorizado y contraseña. Si el usuario no está activo o el dominio no corresponde a Kriamos, el sistema bloqueará el ingreso.','login correo corporativo contraseña ingreso',1),
('Formatos','crear-formato','Cómo diligenciar un formato ICA','Guía para registrar formatos digitales.','Ingrese al menú Formatos, seleccione Nuevo formato, elija el tipo de formato, granja, galpón y ciclo cuando aplique. Diligencie los campos obligatorios. Use No aplica solo cuando corresponda y justifique técnicamente.','formatos ica diligenciar no aplica granja galpon ciclo',1),
('Firmas','firmar-formato','Cómo firmar un formato','Firma digital interna.','Abra el detalle del formato, seleccione Firmar, dibuje la firma en pantalla y confirme con su contraseña. El sistema registra usuario, fecha, hora, IP, dispositivo y hash de firma.','firma firmar aprobar contraseña hash',1),
('Documentos','cargar-documento','Cómo cargar documentos','Carga y clasificación documental.','Ingrese al menú Documentos, seleccione Cargar documento, defina categoría, título, granja y galpón si aplica. Adjunte el archivo permitido y guarde.','documentos cargar pdf word excel evidencia',1),
('Alertas','gestionar-alertas','Cómo gestionar alertas','Estados de alertas y vencimientos.','Ingrese al menú Alertas para revisar eventos pendientes, críticos o vencidos. Puede marcar una alerta como leída, atendida o cerrada según corresponda.','alertas vencimientos certificaciones acciones correctivas',1),
('Reportes','generar-reportes','Cómo generar reportes','Reportes para revisión interna e ICA.','Ingrese a Reportes, seleccione el reporte requerido, aplique filtros y use imprimir o exportar CSV según necesidad.','reportes csv imprimir paquete ica',1),
('Sincronización','modo-offline','Cómo usar modo sin conexión','Uso de PWA y cola offline.','Cuando no tenga internet, el sistema puede guardar registros en cola local del navegador. Al recuperar conexión, use Sincronizar ahora o espere la sincronización automática.','offline sincronizacion indexeddb pwa conexion',1);

INSERT IGNORE INTO manual_pasos (modulo, titulo, descripcion, ruta_sistema, orden) VALUES
('Inicio','Ingresar con correo corporativo','Abra el sistema e ingrese con su correo corporativo autorizado y contraseña.','/login',1),
('Formatos','Crear un nuevo formato','Entre a Formatos y seleccione Nuevo formato. Luego elija el tipo de formato y diligencie los campos requeridos.','/formatos/create',1),
('Formatos','Consultar formatos registrados','Entre a Formatos para ver el listado de registros, estados, granja y usuario que diligenció.','/formatos',2),
('Documentos','Cargar documento','Entre a Documentos y use Cargar documento para subir POE, fichas técnicas, hojas de seguridad o evidencias.','/documentos/create',1),
('Alertas','Revisar alertas','Entre a Alertas para verificar formatos atrasados, certificaciones próximas a vencer o acciones correctivas vencidas.','/alertas',1),
('Reportes','Generar reporte de formatos','Entre a Reportes y seleccione Reporte de formatos. Aplique filtros por fecha, granja, formato o estado.','/reportes/formatos',1),
('Sincronización','Revisar cola offline','Entre a Sincronización para verificar eventos locales y registros recibidos por el servidor.','/sincronizacion',1);

INSERT IGNORE INTO chat_base_conocimiento (pregunta, respuesta, palabras_clave, modulo, prioridad) VALUES
('¿Cómo diligencio un formato?','Ingrese al menú Formatos, seleccione Nuevo formato, escoja el tipo de formato, granja, galpón y ciclo si aplica. Complete los campos obligatorios y guarde.','formato diligenciar crear nuevo granja galpon ciclo','Formatos',10),
('¿Cuándo uso No aplica?','Use No aplica únicamente cuando el formato no corresponda a la fase del ciclo o a la condición operativa. Debe registrar una justificación técnica clara.','no aplica justificacion fase ciclo','Formatos',10),
('¿Cómo firmo un formato?','Abra el detalle del formato, haga clic en Firmar, dibuje la firma en pantalla y confirme con su contraseña. El sistema aprobará el registro y dejará trazabilidad.','firmar firma aprobar contraseña','Firmas',10),
('¿Cómo cargo un documento?','Ingrese a Documentos, seleccione Cargar documento, diligencie título, categoría, granja o galpón si aplica, adjunte el archivo y guarde.','documento cargar subir pdf word excel','Documentos',10),
('¿Cómo reviso las alertas?','Ingrese al menú Alertas. Allí puede ver prioridad, tipo, fecha límite y estado. Puede marcar alertas como leídas, atendidas o cerradas según su rol.','alertas revisar vencimientos estado prioridad','Alertas',10),
('¿Cómo exporto un reporte?','Ingrese a Reportes, abra el reporte requerido, aplique filtros y seleccione CSV/Excel o Imprimir.','reporte exportar csv imprimir excel','Reportes',10),
('¿Qué hago si no tengo internet?','Si está sin conexión, los formularios habilitados se guardan en la cola local. Cuando vuelva internet, entre a Sincronización y use Sincronizar ahora.','sin internet offline sincronizar cola local','Sincronización',10);
