<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class ExamService
{
    public const PASSING_SCORE = 80.0;
    public const QUESTIONS_PER_EXAM = 10;
    public const SECONDS_PER_QUESTION = 60;

    public function findPersonByDocument(PDO $pdo, string $document): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM people WHERE document_number = :document_number AND status = "activo" LIMIT 1');
        $stmt->execute([':document_number' => $document]);
        return $stmt->fetch() ?: null;
    }

    public function findModuleWithCourse(PDO $pdo, int $moduleId): ?array
    {
        $stmt = $pdo->prepare("SELECT m.*, c.title AS course_title, c.code AS course_code, c.target_audience
            FROM course_modules m
            INNER JOIN courses c ON c.id = m.course_id
            WHERE m.id = :id
            LIMIT 1");
        $stmt->execute([':id' => $moduleId]);
        return $stmt->fetch() ?: null;
    }

    public function getAssignedModules(PDO $pdo, array $person): array
    {
        $sql = "SELECT DISTINCT
                    m.id AS module_id,
                    m.title AS module_title,
                    m.description AS module_description,
                    m.sequence_order,
                    m.estimated_minutes,
                    c.id AS course_id,
                    c.code AS course_code,
                    c.title AS course_title,
                    c.description AS course_description,
                    COALESCE(q.active_questions, 0) AS active_questions,
                    last_attempt.id AS last_attempt_id,
                    last_attempt.score AS last_score,
                    last_attempt.status AS last_status,
                    last_attempt.finished_at AS last_finished_at,
                    cert.certificate_code,
                    cert.verification_token AS certificate_token,
                    cert.issued_at AS certificate_issued_at,
                    cert.expires_at AS certificate_expires_at,
                    cert.status AS certificate_status
                FROM course_modules m
                INNER JOIN courses c ON c.id = m.course_id
                INNER JOIN course_assignments ca ON ca.course_id = c.id AND ca.active = 1
                LEFT JOIN (
                    SELECT module_id, COUNT(*) AS active_questions
                    FROM module_questions
                    WHERE status = 'activo'
                    GROUP BY module_id
                ) q ON q.module_id = m.id
                LEFT JOIN exam_attempts last_attempt ON last_attempt.id = (
                    SELECT ea2.id
                    FROM exam_attempts ea2
                    WHERE ea2.person_id = :person_id_last AND ea2.module_id = m.id
                    ORDER BY ea2.started_at DESC, ea2.id DESC
                    LIMIT 1
                )
                LEFT JOIN certificates cert ON cert.id = (
                    SELECT cert2.id
                    FROM certificates cert2
                    WHERE cert2.person_id = :person_id_cert AND cert2.module_id = m.id
                    ORDER BY cert2.issued_at DESC, cert2.id DESC
                    LIMIT 1
                )
                WHERE c.status = 'activo'
                  AND m.status = 'activo'
                  AND (ca.person_type = 'todos' OR ca.person_type = :person_type)
                  AND (ca.work_site = 'todos' OR ca.work_site = :work_site)
                  AND (:person_type_dept = 'contratista' OR ca.department = 'todos' OR ca.department = :department)
                  AND (ca.position_keyword IS NULL OR ca.position_keyword = '' OR :position LIKE CONCAT('%', ca.position_keyword, '%'))
                ORDER BY c.code ASC, m.sequence_order ASC, m.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':person_id_last' => $person['id'],
            ':person_id_cert' => $person['id'],
            ':person_type' => $person['person_type'],
            ':work_site' => $person['work_site'],
            ':person_type_dept' => $person['person_type'],
            ':department' => $person['department'] ?? 'todos',
            ':position' => $person['position'] ?? '',
        ]);
        return $stmt->fetchAll();
    }

    public function canPersonTakeModule(PDO $pdo, array $person, int $moduleId): bool
    {
        foreach ($this->getAssignedModules($pdo, $person) as $module) {
            if ((int)$module['module_id'] === $moduleId) {
                return true;
            }
        }
        return false;
    }

    public function createAttempt(PDO $pdo, array $person, int $moduleId): int
    {
        $module = $this->findModuleWithCourse($pdo, $moduleId);
        if (!$module || $module['status'] !== 'activo') {
            throw new RuntimeException('El módulo no está activo o no existe.');
        }

        if (!$this->canPersonTakeModule($pdo, $person, $moduleId)) {
            throw new RuntimeException('Este módulo no está asignado para el tipo de usuario, sitio de trabajo, equipo o cargo registrado.');
        }

        $excludeQuestionIds = $this->getLatestFailedAttemptQuestionIds($pdo, (int)$person['id'], $moduleId);
        $questions = $this->selectRandomQuestions($pdo, $moduleId, $excludeQuestionIds);

        $stmtNumber = $pdo->prepare('SELECT COALESCE(MAX(attempt_number), 0) + 1 FROM exam_attempts WHERE person_id = :person_id AND module_id = :module_id');
        $stmtNumber->execute([':person_id' => $person['id'], ':module_id' => $moduleId]);
        $attemptNumber = (int)$stmtNumber->fetchColumn();

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO exam_attempts
                (person_id, module_id, attempt_number, total_questions, status, started_at, created_at, updated_at)
                VALUES (:person_id, :module_id, :attempt_number, :total_questions, 'en_progreso', NOW(), NOW(), NOW())");
            $stmt->execute([
                ':person_id' => $person['id'],
                ':module_id' => $moduleId,
                ':attempt_number' => $attemptNumber,
                ':total_questions' => self::QUESTIONS_PER_EXAM,
            ]);
            $attemptId = (int)$pdo->lastInsertId();

            $stmtQuestion = $pdo->prepare("INSERT INTO exam_attempt_questions
                (attempt_id, question_id, question_order, created_at, updated_at)
                VALUES (:attempt_id, :question_id, :question_order, NOW(), NOW())");
            foreach ($questions as $index => $question) {
                $stmtQuestion->execute([
                    ':attempt_id' => $attemptId,
                    ':question_id' => $question['id'],
                    ':question_order' => $index + 1,
                ]);
            }

            $pdo->commit();
            return $attemptId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function getLatestFailedAttemptQuestionIds(PDO $pdo, int $personId, int $moduleId): array
    {
        $stmtAttempt = $pdo->prepare("SELECT id
            FROM exam_attempts
            WHERE person_id = :person_id AND module_id = :module_id AND status = 'reprobado'
            ORDER BY started_at DESC, id DESC
            LIMIT 1");
        $stmtAttempt->execute([':person_id' => $personId, ':module_id' => $moduleId]);
        $lastAttemptId = (int)($stmtAttempt->fetchColumn() ?: 0);
        if ($lastAttemptId <= 0) {
            return [];
        }

        $stmtQuestions = $pdo->prepare('SELECT question_id FROM exam_attempt_questions WHERE attempt_id = :attempt_id');
        $stmtQuestions->execute([':attempt_id' => $lastAttemptId]);
        return array_map('intval', array_column($stmtQuestions->fetchAll(), 'question_id'));
    }

    private function selectRandomQuestions(PDO $pdo, int $moduleId, array $excludeQuestionIds = []): array
    {
        $params = [':module_id' => $moduleId];
        $excludeSql = '';
        if ($excludeQuestionIds) {
            $placeholders = [];
            foreach ($excludeQuestionIds as $index => $questionId) {
                $key = ':exclude_' . $index;
                $placeholders[] = $key;
                $params[$key] = $questionId;
            }
            $excludeSql = ' AND id NOT IN (' . implode(',', $placeholders) . ')';
        }

        $sql = "SELECT id, question_text
            FROM module_questions
            WHERE module_id = :module_id AND status = 'activo'{$excludeSql}
            ORDER BY RAND()
            LIMIT " . self::QUESTIONS_PER_EXAM;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $questions = $stmt->fetchAll();

        if (count($questions) < self::QUESTIONS_PER_EXAM) {
            if ($excludeQuestionIds) {
                throw new RuntimeException('Para repetir este examen con 10 preguntas distintas, el módulo debe tener al menos 20 preguntas activas. Agregue más preguntas al banco antes de permitir un nuevo intento.');
            }
            throw new RuntimeException('El módulo debe tener mínimo 10 preguntas activas antes de iniciar el examen.');
        }

        return $questions;
    }

    public function getAttempt(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare("SELECT ea.*, p.full_name, p.document_number, p.person_type, p.company, p.work_site,
                m.title AS module_title, c.title AS course_title, c.code AS course_code
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            INNER JOIN course_modules m ON m.id = ea.module_id
            INNER JOIN courses c ON c.id = m.course_id
            WHERE ea.id = :id
            LIMIT 1");
        $stmt->execute([':id' => $attemptId]);
        return $stmt->fetch() ?: null;
    }

    public function expireOverdueCurrentQuestion(PDO $pdo, int $attemptId): void
    {
        $stmt = $pdo->prepare("SELECT *
            FROM exam_attempt_questions
            WHERE attempt_id = :attempt_id
              AND locked = 0
              AND displayed_at IS NOT NULL
              AND expires_at IS NOT NULL
              AND expires_at < NOW()
            ORDER BY question_order ASC
            LIMIT 1");
        $stmt->execute([':attempt_id' => $attemptId]);
        $attemptQuestion = $stmt->fetch();
        if (!$attemptQuestion) {
            return;
        }

        $pdo->beginTransaction();
        try {
            $this->insertAnswer($pdo, (int)$attemptQuestion['id'], null, false, self::SECONDS_PER_QUESTION);
            $this->lockAttemptQuestion($pdo, (int)$attemptQuestion['id']);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getCurrentQuestion(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare("SELECT aq.*, q.question_text, q.difficulty, q.explanation
            FROM exam_attempt_questions aq
            INNER JOIN module_questions q ON q.id = aq.question_id
            WHERE aq.attempt_id = :attempt_id AND aq.locked = 0
            ORDER BY aq.question_order ASC
            LIMIT 1");
        $stmt->execute([':attempt_id' => $attemptId]);
        $current = $stmt->fetch() ?: null;
        if (!$current) {
            return null;
        }

        if (empty($current['displayed_at'])) {
            $stmtUpdate = $pdo->prepare("UPDATE exam_attempt_questions
                SET displayed_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 60 SECOND), updated_at = NOW()
                WHERE id = :id");
            $stmtUpdate->execute([':id' => $current['id']]);

            $stmtReload = $pdo->prepare("SELECT aq.*, q.question_text, q.difficulty, q.explanation
                FROM exam_attempt_questions aq
                INNER JOIN module_questions q ON q.id = aq.question_id
                WHERE aq.id = :id
                LIMIT 1");
            $stmtReload->execute([':id' => $current['id']]);
            $current = $stmtReload->fetch() ?: $current;
        }

        return $current;
    }

    public function getQuestionOptions(PDO $pdo, int $questionId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM question_options WHERE question_id = :question_id ORDER BY option_label ASC');
        $stmt->execute([':question_id' => $questionId]);
        return $stmt->fetchAll();
    }

    public function getAttemptProgress(PDO $pdo, int $attemptId): array
    {
        $stmt = $pdo->prepare("SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN locked = 1 THEN 1 ELSE 0 END) AS answered
            FROM exam_attempt_questions
            WHERE attempt_id = :attempt_id");
        $stmt->execute([':attempt_id' => $attemptId]);
        $row = $stmt->fetch() ?: ['total' => 0, 'answered' => 0];
        return ['total' => (int)$row['total'], 'answered' => (int)$row['answered']];
    }

    public function saveAnswer(PDO $pdo, int $attemptId, int $attemptQuestionId, ?int $selectedOptionId): void
    {
        $stmt = $pdo->prepare("SELECT aq.*, ea.status
            FROM exam_attempt_questions aq
            INNER JOIN exam_attempts ea ON ea.id = aq.attempt_id
            WHERE aq.id = :id AND aq.attempt_id = :attempt_id
            LIMIT 1");
        $stmt->execute([':id' => $attemptQuestionId, ':attempt_id' => $attemptId]);
        $attemptQuestion = $stmt->fetch();

        if (!$attemptQuestion || $attemptQuestion['status'] !== 'en_progreso' || (int)$attemptQuestion['locked'] === 1) {
            return;
        }

        if (empty($attemptQuestion['displayed_at']) || empty($attemptQuestion['expires_at'])) {
            return;
        }

        $responseSeconds = $this->calculateResponseSeconds($attemptQuestion['displayed_at']);
        $isExpired = strtotime((string)$attemptQuestion['expires_at']) < time();
        $isCorrect = false;
        $validSelectedOptionId = null;

        if (!$isExpired && $selectedOptionId) {
            $stmtOption = $pdo->prepare("SELECT *
                FROM question_options
                WHERE id = :id AND question_id = :question_id
                LIMIT 1");
            $stmtOption->execute([':id' => $selectedOptionId, ':question_id' => $attemptQuestion['question_id']]);
            $option = $stmtOption->fetch();
            if ($option) {
                $validSelectedOptionId = (int)$option['id'];
                $isCorrect = (int)$option['is_correct'] === 1;
            }
        }

        $pdo->beginTransaction();
        try {
            $this->insertAnswer($pdo, $attemptQuestionId, $validSelectedOptionId, $isCorrect, min($responseSeconds, self::SECONDS_PER_QUESTION));
            $this->lockAttemptQuestion($pdo, $attemptQuestionId);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function insertAnswer(PDO $pdo, int $attemptQuestionId, ?int $selectedOptionId, bool $isCorrect, ?int $responseSeconds): void
    {
        $stmt = $pdo->prepare("INSERT IGNORE INTO exam_answers
            (attempt_question_id, selected_option_id, is_correct, answered_at, response_seconds, created_at, updated_at)
            VALUES (:attempt_question_id, :selected_option_id, :is_correct, NOW(), :response_seconds, NOW(), NOW())");
        $stmt->execute([
            ':attempt_question_id' => $attemptQuestionId,
            ':selected_option_id' => $selectedOptionId,
            ':is_correct' => $isCorrect ? 1 : 0,
            ':response_seconds' => $responseSeconds,
        ]);
    }

    private function lockAttemptQuestion(PDO $pdo, int $attemptQuestionId): void
    {
        $stmt = $pdo->prepare("UPDATE exam_attempt_questions
            SET locked = 1, answered_at = NOW(), updated_at = NOW()
            WHERE id = :id AND locked = 0");
        $stmt->execute([':id' => $attemptQuestionId]);
    }

    private function calculateResponseSeconds(string $displayedAt): int
    {
        $start = strtotime($displayedAt);
        if (!$start) {
            return 0;
        }
        return max(0, time() - $start);
    }

    public function finalizeAttemptIfComplete(PDO $pdo, int $attemptId): ?array
    {
        $progress = $this->getAttemptProgress($pdo, $attemptId);
        if ($progress['total'] === 0 || $progress['answered'] < $progress['total']) {
            return null;
        }

        $attempt = $this->getAttempt($pdo, $attemptId);
        if (!$attempt) {
            return null;
        }
        if ($attempt['status'] !== 'en_progreso') {
            return $attempt;
        }

        $stmtCorrect = $pdo->prepare("SELECT COUNT(*)
            FROM exam_answers ans
            INNER JOIN exam_attempt_questions aq ON aq.id = ans.attempt_question_id
            WHERE aq.attempt_id = :attempt_id AND ans.is_correct = 1");
        $stmtCorrect->execute([':attempt_id' => $attemptId]);
        $correct = (int)$stmtCorrect->fetchColumn();
        $total = max(1, (int)$attempt['total_questions']);
        $score = round(($correct / $total) * 100, 2);
        $passed = $score >= self::PASSING_SCORE;
        $status = $passed ? 'aprobado' : 'reprobado';

        $stmtUpdate = $pdo->prepare("UPDATE exam_attempts
            SET correct_answers = :correct_answers,
                score = :score,
                passed = :passed,
                status = :status,
                finished_at = NOW(),
                updated_at = NOW()
            WHERE id = :id");
        $stmtUpdate->execute([
            ':correct_answers' => $correct,
            ':score' => $score,
            ':passed' => $passed ? 1 : 0,
            ':status' => $status,
            ':id' => $attemptId,
        ]);

        return $this->getAttempt($pdo, $attemptId);
    }

    public function getAttemptReview(PDO $pdo, int $attemptId): array
    {
        $stmt = $pdo->prepare("SELECT
                aq.question_order,
                q.question_text,
                q.explanation,
                selected.option_label AS selected_label,
                selected.option_text AS selected_text,
                correct.option_label AS correct_label,
                correct.option_text AS correct_text,
                ans.is_correct,
                ans.response_seconds
            FROM exam_attempt_questions aq
            INNER JOIN module_questions q ON q.id = aq.question_id
            LEFT JOIN exam_answers ans ON ans.attempt_question_id = aq.id
            LEFT JOIN question_options selected ON selected.id = ans.selected_option_id
            LEFT JOIN question_options correct ON correct.question_id = q.id AND correct.is_correct = 1
            WHERE aq.attempt_id = :attempt_id
            ORDER BY aq.question_order ASC");
        $stmt->execute([':attempt_id' => $attemptId]);
        return $stmt->fetchAll();
    }
}
