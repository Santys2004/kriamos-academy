-- Fase 7 - Academia KRIAMOS
-- Sistema de recompensas para empleados por calificación obtenida.
-- Ejecutar después de 006_create_dashboard_metrics.sql.

CREATE TABLE IF NOT EXISTS reward_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    min_score DECIMAL(5,2) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    reward_description VARCHAR(180) NOT NULL,
    frozen_chickens_qty INT UNSIGNED NOT NULL DEFAULT 0,
    only_employees TINYINT(1) NOT NULL DEFAULT 1,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_reward_rules_score (active, min_score, max_score),
    INDEX idx_reward_rules_employees (only_employees)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    person_id BIGINT UNSIGNED NOT NULL,
    module_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    exam_attempt_id BIGINT UNSIGNED NOT NULL,
    reward_rule_id INT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    frozen_chickens_qty INT UNSIGNED NOT NULL DEFAULT 0,
    reward_description VARCHAR(180) NOT NULL,
    status ENUM('pendiente','entregada','anulada') NOT NULL DEFAULT 'pendiente',
    earned_at DATETIME NOT NULL,
    delivered_at DATETIME NULL,
    delivered_by VARCHAR(180) NULL,
    delivery_notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_rewards_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE,
    CONSTRAINT fk_rewards_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    CONSTRAINT fk_rewards_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_rewards_attempt FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_rewards_rule FOREIGN KEY (reward_rule_id) REFERENCES reward_rules(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_rewards_attempt (exam_attempt_id),
    INDEX idx_rewards_person_status (person_id, status),
    INDEX idx_rewards_status_earned (status, earned_at),
    INDEX idx_rewards_course_module (course_id, module_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO reward_rules
    (name, min_score, max_score, reward_description, frozen_chickens_qty, only_employees, active, created_at, updated_at)
SELECT 'Excelencia total', 100.00, 100.00, '4 pollos congelados', 4, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM reward_rules WHERE name = 'Excelencia total');

INSERT INTO reward_rules
    (name, min_score, max_score, reward_description, frozen_chickens_qty, only_employees, active, created_at, updated_at)
SELECT 'Alto desempeño', 90.00, 99.99, '1 pollo congelado', 1, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM reward_rules WHERE name = 'Alto desempeño');
