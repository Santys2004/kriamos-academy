USE kriamos_biogestion_ica;

-- Permisos complementarios para Fase 05.
INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('formatos','detalle','formatos.detalle','Ver detalle de formatos diligenciados.'),
('formatos','no_aplica','formatos.no_aplica','Marcar formatos como No aplica con justificación.'),
('formatos','exportar','formatos.exportar','Exportar formatos.'),
('formatos','imprimir','formatos.imprimir','Imprimir formatos.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('formatos.detalle','formatos.no_aplica','formatos.exportar','formatos.imprimir')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('formatos.detalle','formatos.no_aplica','formatos.imprimir')
WHERE r.nombre IN ('Administrador de granja','Médico veterinario / Responsable sanitario');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('formatos.detalle','formatos.imprimir')
WHERE r.nombre IN ('Gerencia / Dirección','Auditor interno');

-- Tabla de guías normativas y de diligenciamiento por formato.
CREATE TABLE IF NOT EXISTS formato_guias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_formato_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    resumen TEXT NOT NULL,
    guia_json JSON NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_formato_guias_tipos FOREIGN KEY (tipo_formato_id) REFERENCES tipos_formato(id)
) ENGINE=InnoDB;

-- Tabla para futuras definiciones de campos por base de datos.
CREATE TABLE IF NOT EXISTS formato_campos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_formato_id BIGINT UNSIGNED NOT NULL,
    nombre_campo VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(160) NOT NULL,
    tipo_campo ENUM('text','textarea','number','date','time','select','checkbox') NOT NULL DEFAULT 'text',
    opciones_json JSON NULL,
    requerido TINYINT(1) NOT NULL DEFAULT 0,
    orden INT UNSIGNED NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_formato_campos_tipos FOREIGN KEY (tipo_formato_id) REFERENCES tipos_formato(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_formatos_registros_fecha ON formatos_registros (fecha_formato);
CREATE INDEX IF NOT EXISTS idx_formatos_registros_no_aplica ON formatos_registros (es_no_aplica);
CREATE INDEX IF NOT EXISTS idx_formato_guias_tipo ON formato_guias (tipo_formato_id);
CREATE INDEX IF NOT EXISTS idx_formato_campos_tipo ON formato_campos (tipo_formato_id);

-- Carga inicial de guías generales.
INSERT INTO formato_guias (tipo_formato_id, titulo, resumen, guia_json)
SELECT id, nombre, descripcion,
       JSON_OBJECT(
           'recomendaciones', JSON_ARRAY(
               'Diligencie información real, completa y verificable.',
               'Seleccione granja, galpón y ciclo cuando corresponda.',
               'Use No aplica únicamente con justificación técnica.',
               'Revise la información antes de guardar.'
           ),
           'referencia', fundamento
       )
FROM tipos_formato
WHERE NOT EXISTS (
    SELECT 1 FROM formato_guias fg WHERE fg.tipo_formato_id = tipos_formato.id
);
