-- Fase 9 - Academia KRIAMOS
-- Chat IA con base de conocimiento interna, trazabilidad de conversaciones y preguntas frecuentes.
-- Ejecutar después de 008_create_offline_downloads.sql.

CREATE TABLE IF NOT EXISTS ai_knowledge_base (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    keywords TEXT NULL,
    source_type ENUM('poe','manual','curso','app','faq','otro') NOT NULL DEFAULT 'faq',
    target_audience ENUM('todos','empleado','contratista') NOT NULL DEFAULT 'todos',
    work_site ENUM('todos','GAEE','GALM','Oficina') NOT NULL DEFAULT 'todos',
    department ENUM('todos','Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos') NOT NULL DEFAULT 'todos',
    status ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_by VARCHAR(120) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FULLTEXT KEY ft_ai_knowledge (title, content, keywords),
    INDEX idx_ai_knowledge_filters (target_audience, work_site, department, status),
    INDEX idx_ai_knowledge_source (source_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_chat_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(80) NOT NULL UNIQUE,
    channel ENUM('web','pwa','admin') NOT NULL DEFAULT 'web',
    status ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
    started_at DATETIME NOT NULL,
    last_interaction_at DATETIME NOT NULL,
    CONSTRAINT fk_ai_chat_sessions_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    INDEX idx_ai_chat_sessions_person (person_id),
    INDEX idx_ai_chat_sessions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    sender ENUM('user','assistant','system') NOT NULL,
    message MEDIUMTEXT NOT NULL,
    matched_knowledge_ids TEXT NULL,
    confidence_score DECIMAL(8,2) NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_ai_chat_messages_session FOREIGN KEY (session_id) REFERENCES ai_chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_ai_chat_messages_session (session_id, created_at),
    INDEX idx_ai_chat_messages_sender (sender)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO ai_knowledge_base
(id, title, content, keywords, source_type, target_audience, work_site, department, status, created_by, created_at, updated_at) VALUES
(1, 'Uso de la aplicación Academia KRIAMOS', 'Para ingresar a Academia KRIAMOS, el trabajador o contratista debe estar registrado en la planilla digital con su cédula, datos personales, sitio de trabajo, empresa, cargo y foto. El acceso operativo no requiere contraseña. La cédula se usa para consultar capacitaciones, calendario, certificados, recompensas y materiales offline.', 'registro cedula app contraseña planilla foto academia kriamos ingreso plataforma', 'app', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(2, 'Regla del examen de capacitación', 'Cada módulo se evalúa con 10 preguntas. Cada pregunta tiene un tiempo máximo de 1 minuto. Después de enviar una respuesta, no se puede modificar. La nota mínima aprobatoria es 80%, equivalente a 8 respuestas correctas de 10. Si no se aprueba, el sistema debe generar un nuevo intento con preguntas diferentes.', 'examen preguntas cronometro tiempo respuesta aprobar 80 reintento', 'app', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(3, 'Certificación y recertificación', 'Cuando una persona aprueba un módulo, se genera un certificado con código único, QR, fecha de aprobación y vigencia de seis meses. El sistema programa la recertificación y envía recordatorios por correo 5 días antes y 1 día antes de la nueva activación.', 'certificado qr recertificacion seis meses vigencia correo recordatorio calendario', 'app', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(4, 'Sistema de recompensas para empleados', 'El sistema de recompensas aplica únicamente para empleados. Si el empleado obtiene 100% recibe 4 pollos congelados. Si obtiene entre 90% y 99,99% recibe 1 pollo congelado. Los contratistas no generan recompensas.', 'recompensa pollos congelados empleados contratistas 100 90 premio', 'app', 'empleado', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(5, 'Bioseguridad para ingreso a granjas', 'Toda persona que ingrese a instalaciones avícolas debe cumplir los protocolos de bioseguridad establecidos por KRIAMOS. Debe registrarse, seguir las indicaciones de ingreso, respetar la zona sucia, zona intermedia y zona limpia, ducharse cuando aplique, usar dotación autorizada y desinfectar objetos estrictamente necesarios según el procedimiento definido.', 'bioseguridad ingreso granja zona sucia intermedia limpia ducha dotacion objetos pediluvio', 'poe', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(6, 'Bienestar animal en pollo de engorde', 'El bienestar animal en KRIAMOS se orienta a garantizar aves sanas, cómodas, bien alimentadas, con acceso permanente a agua, ambiente adecuado, manejo tranquilo, reducción del dolor, miedo, estrés y sufrimiento evitable. Incluye recepción de pollito, alimento, agua, cama, ventilación, iluminación, manejo sanitario, emergencias y registros.', 'bienestar animal pollo engorde agua alimento cama ventilacion iluminacion estres aves', 'poe', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(7, 'Emergencias sanitarias y enfermedades de control oficial', 'Ante sospecha de Influenza Aviar, Newcastle, Salmonella, alta mortalidad súbita o signos severos, se debe informar de inmediato al responsable sanitario, administrador, médico veterinario o Dirección de Operaciones. El personal no debe tomar decisiones no autorizadas ni movilizar aves, equipos o materiales sin instrucción del responsable.', 'influenza newcastle salmonella mortalidad emergencia sanitaria ica cuarentena notificar', 'poe', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(8, 'Modo sin conexión', 'El modo sin conexión permite descargar y estudiar materiales previamente autorizados, como manuales, videos, podcast, PDF, Word, PowerPoint e imágenes. El examen oficial debe presentarse con conexión para asegurar cronómetro, trazabilidad, bloqueo de respuestas y validación contra el servidor.', 'offline sin conexion descargar materiales estudiar examen conexion pwa', 'app', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW()),
(9, 'Cuándo escalar una consulta', 'Las preguntas relacionadas con diagnóstico, medicamentos, enfermedades, mortalidad alta, sacrificio humanitario, contingencias sanitarias o decisiones técnicas deben escalarse al Médico Veterinario, administrador, Dirección de Operaciones o autoridad sanitaria competente según el caso. El chat es una ayuda de orientación y no reemplaza la autoridad técnica.', 'escalar medico veterinario administrador operaciones autoridad sanitaria decision tecnica', 'faq', 'todos', 'todos', 'todos', 'activo', 'Sistema', NOW(), NOW());
