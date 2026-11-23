-- Fase 9 - Academia KRIAMOS
-- PWA y modo sin conexión para materiales descargables.
-- Ejecutar después de 007_create_rewards.sql.

CREATE TABLE IF NOT EXISTS offline_downloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id INT UNSIGNED NOT NULL,
    material_id INT UNSIGNED NOT NULL,
    device_info VARCHAR(255) NULL,
    downloaded_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_offline_downloads_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_offline_downloads_material FOREIGN KEY (material_id) REFERENCES course_materials(id) ON DELETE CASCADE,
    UNIQUE KEY uq_offline_person_material (person_id, material_id),
    INDEX idx_offline_downloads_person (person_id),
    INDEX idx_offline_downloads_material (material_id),
    INDEX idx_offline_downloads_date (downloaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
