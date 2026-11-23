-- Fase 6 - Academia KRIAMOS
-- Dashboard gerencial, medición de tiempo dedicado y adquisición de habilidades.
-- Ejecutar después de 001, 002, 003, 004 y 005.

CREATE TABLE IF NOT EXISTS module_skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id INT UNSIGNED NOT NULL,
    skill_name VARCHAR(140) NOT NULL,
    skill_category ENUM('bioseguridad','bienestar_animal','operacion','mantenimiento','sg_sst','ambiental','emergencias','gestion_documental','otro') NOT NULL DEFAULT 'otro',
    weight DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uk_module_skill (module_id, skill_name),
    KEY idx_module_skills_category (skill_category),
    CONSTRAINT fk_module_skills_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NULL,
    module_id INT UNSIGNED NULL,
    activity_type ENUM('material','video','podcast','lectura','examen','repaso','otro') NOT NULL DEFAULT 'otro',
    activity_title VARCHAR(180) NULL,
    started_at DATETIME NOT NULL,
    finished_at DATETIME NULL,
    duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    source VARCHAR(80) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_learning_logs_person_date (person_id, started_at),
    KEY idx_learning_logs_module (module_id),
    KEY idx_learning_logs_type (activity_type),
    CONSTRAINT fk_learning_logs_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_learning_logs_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    CONSTRAINT fk_learning_logs_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Etiquetado inicial automático de habilidades según el nombre del curso y módulo.
-- Puede ajustarse manualmente desde base de datos o desde una fase posterior de administración avanzada.
INSERT IGNORE INTO module_skills (module_id, skill_name, skill_category, weight, created_at, updated_at)
SELECT
    m.id,
    CASE
        WHEN c.title LIKE '%Bioseguridad%' THEN 'Bioseguridad'
        WHEN c.title LIKE '%Bienestar%' THEN 'Bienestar animal'
        WHEN c.title LIKE '%Mantenimiento%' OR m.title LIKE '%Fracttal%' THEN 'Mantenimiento y equipos críticos'
        WHEN c.title LIKE '%Emergencia%' OR m.title LIKE '%Emergencia%' THEN 'Respuesta ante emergencias'
        WHEN c.title LIKE '%Residuo%' OR m.title LIKE '%Residuo%' THEN 'Gestión ambiental y residuos'
        WHEN c.title LIKE '%SG-SST%' OR m.title LIKE '%seguridad%' THEN 'Trabajo seguro y SG-SST'
        ELSE 'Competencia operativa avícola'
    END AS skill_name,
    CASE
        WHEN c.title LIKE '%Bioseguridad%' THEN 'bioseguridad'
        WHEN c.title LIKE '%Bienestar%' THEN 'bienestar_animal'
        WHEN c.title LIKE '%Mantenimiento%' OR m.title LIKE '%Fracttal%' THEN 'mantenimiento'
        WHEN c.title LIKE '%Emergencia%' OR m.title LIKE '%Emergencia%' THEN 'emergencias'
        WHEN c.title LIKE '%Residuo%' OR m.title LIKE '%Residuo%' THEN 'ambiental'
        WHEN c.title LIKE '%SG-SST%' OR m.title LIKE '%seguridad%' THEN 'sg_sst'
        ELSE 'operacion'
    END AS skill_category,
    1.00,
    NOW(),
    NOW()
FROM course_modules m
INNER JOIN courses c ON c.id = m.course_id;
