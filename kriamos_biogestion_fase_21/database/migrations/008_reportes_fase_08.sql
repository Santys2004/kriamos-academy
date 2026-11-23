USE kriamos_biogestion_ica;

INSERT IGNORE INTO permisos (modulo, accion, codigo, descripcion) VALUES
('reportes','exportar','reportes.exportar','Exportar reportes en CSV, ZIP o formatos descargables.'),
('reportes','imprimir','reportes.imprimir','Imprimir reportes desde el navegador.'),
('reportes','paquete_ica','reportes.paquete_ica','Generar paquete documental ICA.');

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('reportes.exportar','reportes.imprimir','reportes.paquete_ica')
WHERE r.nombre = 'Administrador del sistema';

INSERT IGNORE INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r
JOIN permisos p ON p.codigo IN ('reportes.exportar','reportes.imprimir','reportes.paquete_ica')
WHERE r.nombre IN ('Gerencia / Dirección','Médico veterinario / Responsable sanitario','Administrador de granja','Auditor interno');

CREATE TABLE IF NOT EXISTS reportes_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    tipo_reporte VARCHAR(100) NOT NULL,
    filtros_json JSON NULL,
    formato_salida ENUM('html','csv','pdf','zip','excel') NOT NULL DEFAULT 'html',
    archivo_path VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reportes_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_reportes_historial_usuario ON reportes_historial (usuario_id);
CREATE INDEX IF NOT EXISTS idx_reportes_historial_tipo ON reportes_historial (tipo_reporte);
CREATE INDEX IF NOT EXISTS idx_reportes_historial_fecha ON reportes_historial (created_at);
