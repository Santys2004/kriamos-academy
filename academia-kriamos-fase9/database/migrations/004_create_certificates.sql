-- Fase 4 - Academia KRIAMOS
-- Certificados, codigo QR, vigencia de seis meses e historial.
-- Ejecutar después de 001, 002 y 003.

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_code VARCHAR(80) NOT NULL,
    verification_token VARCHAR(80) NOT NULL,
    person_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NOT NULL,
    exam_attempt_id BIGINT UNSIGNED NOT NULL,
    issued_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    status ENUM('vigente','vencido','revocado') NOT NULL DEFAULT 'vigente',
    pdf_path VARCHAR(500) NULL,
    qr_path VARCHAR(500) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uk_certificates_code (certificate_code),
    UNIQUE KEY uk_certificates_token (verification_token),
    UNIQUE KEY uk_certificates_attempt (exam_attempt_id),
    INDEX idx_certificates_person_module (person_id, module_id),
    INDEX idx_certificates_status_expires (status, expires_at),
    CONSTRAINT fk_certificates_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_attempt FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
