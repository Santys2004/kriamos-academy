CREATE DATABASE IF NOT EXISTS academia_kriamos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE academia_kriamos;

CREATE TABLE IF NOT EXISTS people (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_type ENUM('empleado', 'contratista') NOT NULL,
    full_name VARCHAR(180) NOT NULL,
    document_number VARCHAR(30) NOT NULL,
    residence VARCHAR(180) NOT NULL,
    work_site ENUM('GAEE', 'GALM', 'Oficina') NOT NULL,
    cellphone VARCHAR(40) NOT NULL,
    email VARCHAR(180) NOT NULL,
    company VARCHAR(180) NOT NULL,
    position VARCHAR(140) NOT NULL,
    department ENUM('Operaciones', 'Mantenimiento', 'Compras', 'Gestión Humana', 'Administrativos') NULL,
    photo_path VARCHAR(255) NULL,
    status ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uk_people_document_number (document_number),
    KEY idx_people_type (person_type),
    KEY idx_people_work_site (work_site),
    KEY idx_people_department (department),
    KEY idx_people_company (company)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
