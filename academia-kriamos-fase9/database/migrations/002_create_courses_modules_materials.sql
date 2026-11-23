-- Fase 2 - Academia KRIAMOS
-- Administración de cursos, módulos, materiales y asignaciones.
-- Ejecutar después de 001_create_people_table.sql.

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    target_audience ENUM('todos','empleados','contratistas') NOT NULL DEFAULT 'todos',
    status ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_courses_target_status (target_audience, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_modules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    sequence_order INT UNSIGNED NOT NULL DEFAULT 1,
    estimated_minutes INT UNSIGNED NOT NULL DEFAULT 15,
    status ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_course_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_modules_course_order (course_id, sequence_order),
    INDEX idx_course_modules_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id INT UNSIGNED NOT NULL,
    material_type ENUM('manual','video','podcast','pdf','word','powerpoint','imagen','otro') NOT NULL DEFAULT 'pdf',
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(150) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    is_downloadable TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_course_materials_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    INDEX idx_course_materials_module (module_id),
    INDEX idx_course_materials_type (material_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    person_type ENUM('todos','empleado','contratista') NOT NULL DEFAULT 'todos',
    work_site ENUM('todos','GAEE','GALM','Oficina') NOT NULL DEFAULT 'todos',
    department ENUM('todos','Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos') NOT NULL DEFAULT 'todos',
    position_keyword VARCHAR(120) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_course_assignments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_assignments_filters (person_type, work_site, department, active),
    INDEX idx_course_assignments_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cursos sugeridos iniciales. Puede eliminarlos o ajustarlos si desea iniciar vacío.
INSERT IGNORE INTO courses (code, title, description, target_audience, status, created_at, updated_at) VALUES
('KRI-CAP-001', 'Inducción general KRIAMOS y cultura biosegura', 'Curso base para empleados y contratistas que ingresan a instalaciones avícolas de KRIAMOS.', 'todos', 'activo', NOW(), NOW()),
('KRI-CAP-002', 'Bioseguridad para ingreso de personas, vehículos y objetos', 'Protocolos de ingreso, filtros sanitarios, zonas, cabina de desinfección y conducta sanitaria.', 'todos', 'activo', NOW(), NOW()),
('KRI-CAP-003', 'Bienestar animal en pollo de engorde', 'Cinco libertades, manejo diario, cama, agua, alimento, ventilación, aves enfermas y emergencias.', 'todos', 'activo', NOW(), NOW()),
('KRI-CAP-004', 'Mantenimiento de equipos críticos y uso de Fracttal', 'Capacitación para equipos de mantenimiento, administradores y personal operativo relacionado con equipos críticos.', 'empleados', 'activo', NOW(), NOW());
