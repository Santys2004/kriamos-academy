-- Fase 5 - Academia KRIAMOS
-- Calendario de recertificación, reactivación cada seis meses y alertas por correo.
-- Ejecutar después de 001, 002, 003 y 004.

CREATE TABLE IF NOT EXISTS training_calendar (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NOT NULL,
    certificate_id BIGINT UNSIGNED NOT NULL,
    last_passed_at DATETIME NOT NULL,
    next_due_at DATETIME NOT NULL,
    reminder_5d_at DATETIME NOT NULL,
    reminder_1d_at DATETIME NOT NULL,
    status ENUM('vigente','por_vencer','vencido','reactivado') NOT NULL DEFAULT 'vigente',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uk_training_calendar_certificate (certificate_id),
    KEY idx_training_calendar_person_due (person_id, next_due_at),
    KEY idx_training_calendar_status_due (status, next_due_at),
    KEY idx_training_calendar_module (module_id),
    CONSTRAINT fk_training_calendar_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_calendar_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_calendar_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_training_calendar_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    training_calendar_id BIGINT UNSIGNED NOT NULL,
    person_id BIGINT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NOT NULL,
    certificate_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('recordatorio_5_dias','recordatorio_1_dia','reactivacion','manual') NOT NULL,
    channel ENUM('email') NOT NULL DEFAULT 'email',
    recipient_email VARCHAR(180) NOT NULL,
    subject VARCHAR(220) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    sent_at DATETIME NULL,
    status ENUM('pendiente','enviado','fallido','omitido') NOT NULL DEFAULT 'pendiente',
    error_message TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uk_notification_calendar_type (training_calendar_id, notification_type),
    KEY idx_notification_status_schedule (status, scheduled_at),
    KEY idx_notification_person (person_id),
    CONSTRAINT fk_notification_calendar FOREIGN KEY (training_calendar_id) REFERENCES training_calendar(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
