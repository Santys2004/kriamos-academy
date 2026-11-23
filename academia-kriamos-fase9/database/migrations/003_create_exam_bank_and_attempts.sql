-- Fase 3 - Academia KRIAMOS
-- Banco de preguntas, opciones, intentos de examen, cronómetro y calificación.
-- Ejecutar después de 001_create_people_table.sql y 002_create_courses_modules_materials.sql.

CREATE TABLE IF NOT EXISTS module_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    explanation TEXT NULL,
    difficulty ENUM('basica','media','critica') NOT NULL DEFAULT 'basica',
    status ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_module_questions_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    INDEX idx_module_questions_module_status (module_id, status),
    INDEX idx_module_questions_difficulty (difficulty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS question_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    option_label CHAR(1) NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_question_options_question FOREIGN KEY (question_id) REFERENCES module_questions(id) ON DELETE CASCADE,
    UNIQUE KEY uk_question_option_label (question_id, option_label),
    INDEX idx_question_options_correct (question_id, is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NOT NULL,
    attempt_number INT UNSIGNED NOT NULL DEFAULT 1,
    total_questions TINYINT UNSIGNED NOT NULL DEFAULT 10,
    correct_answers TINYINT UNSIGNED NOT NULL DEFAULT 0,
    score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('en_progreso','aprobado','reprobado','anulado') NOT NULL DEFAULT 'en_progreso',
    started_at DATETIME NOT NULL,
    finished_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_exam_attempts_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempts_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    INDEX idx_exam_attempts_person_module (person_id, module_id),
    INDEX idx_exam_attempts_status (status),
    INDEX idx_exam_attempts_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_attempt_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    question_order TINYINT UNSIGNED NOT NULL,
    displayed_at DATETIME NULL,
    expires_at DATETIME NULL,
    answered_at DATETIME NULL,
    locked TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_exam_attempt_questions_attempt FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempt_questions_question FOREIGN KEY (question_id) REFERENCES module_questions(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_attempt_question (attempt_id, question_id),
    UNIQUE KEY uk_attempt_order (attempt_id, question_order),
    INDEX idx_exam_attempt_questions_current (attempt_id, locked, question_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exam_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_question_id BIGINT UNSIGNED NOT NULL,
    selected_option_id INT UNSIGNED NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    answered_at DATETIME NOT NULL,
    response_seconds INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_exam_answers_attempt_question FOREIGN KEY (attempt_question_id) REFERENCES exam_attempt_questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_answers_option FOREIGN KEY (selected_option_id) REFERENCES question_options(id) ON DELETE SET NULL,
    UNIQUE KEY uk_exam_answers_attempt_question (attempt_question_id),
    INDEX idx_exam_answers_correct (is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
