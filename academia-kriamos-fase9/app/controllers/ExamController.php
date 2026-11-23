<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/ExamService.php';
require_once __DIR__ . '/../services/CertificateService.php';
require_once __DIR__ . '/../services/RewardService.php';

class ExamController
{
    private ExamService $service;

    public function __construct()
    {
        $this->service = new ExamService();
    }

    public function questionBank(): void
    {
        $moduleId = (int)($_GET['module_id'] ?? 0);
        try {
            $pdo = Database::connection();
            $module = $this->service->findModuleWithCourse($pdo, $moduleId);
            if (!$module) {
                throw new RuntimeException('Módulo no encontrado.');
            }

            $stmt = $pdo->prepare("SELECT q.*,
                    COUNT(o.id) AS options_count,
                    SUM(CASE WHEN o.is_correct = 1 THEN 1 ELSE 0 END) AS correct_options_count
                FROM module_questions q
                LEFT JOIN question_options o ON o.question_id = q.id
                WHERE q.module_id = :module_id
                GROUP BY q.id
                ORDER BY q.created_at DESC, q.id DESC");
            $stmt->execute([':module_id' => $moduleId]);
            $questions = $stmt->fetchAll();

            view('preguntas_modulo', [
                'title' => 'Banco de preguntas',
                'module' => $module,
                'questions' => $questions,
                'success' => $_GET['success'] ?? null,
            ]);
        } catch (Throwable $e) {
            view('admin_cursos', ['title' => 'Administración de cursos', 'courses' => [], 'error' => $e->getMessage()]);
        }
    }

    public function createQuestion(): void
    {
        $moduleId = (int)($_GET['module_id'] ?? 0);
        try {
            $pdo = Database::connection();
            $module = $this->service->findModuleWithCourse($pdo, $moduleId);
            if (!$module) {
                throw new RuntimeException('Módulo no encontrado.');
            }

            view('pregunta_form', [
                'title' => 'Crear pregunta',
                'module' => $module,
                'errors' => [],
                'old' => ['difficulty' => 'basica', 'status' => 'activo', 'correct_option' => 'A'],
            ]);
        } catch (Throwable $e) {
            redirect(current_url('admin_cursos'));
        }
    }

    public function storeQuestion(): void
    {
        $data = $_POST;
        $moduleId = (int)($data['module_id'] ?? 0);
        $errors = validate_required($data, [
            'module_id' => 'módulo',
            'question_text' => 'pregunta',
            'correct_option' => 'respuesta correcta',
        ]);

        $labels = ['A', 'B', 'C', 'D'];
        foreach ($labels as $label) {
            if (trim((string)($data['option_' . $label] ?? '')) === '') {
                $errors['option_' . $label] = "La opción {$label} es obligatoria.";
            }
        }

        $difficulty = $data['difficulty'] ?? 'basica';
        $status = $data['status'] ?? 'activo';
        $correctOption = $data['correct_option'] ?? 'A';
        if (!in_array($difficulty, ['basica', 'media', 'critica'], true)) {
            $errors['difficulty'] = 'Seleccione una dificultad válida.';
        }
        if (!in_array($status, ['activo', 'inactivo'], true)) {
            $errors['status'] = 'Seleccione un estado válido.';
        }
        if (!in_array($correctOption, $labels, true)) {
            $errors['correct_option'] = 'Seleccione una respuesta correcta válida.';
        }

        try {
            $pdo = Database::connection();
            $module = $this->service->findModuleWithCourse($pdo, $moduleId);
            if (!$module) {
                throw new RuntimeException('Módulo no encontrado.');
            }

            if ($errors) {
                view('pregunta_form', ['title' => 'Crear pregunta', 'module' => $module, 'errors' => $errors, 'old' => $data]);
                return;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO module_questions
                (module_id, question_text, explanation, difficulty, status, created_at, updated_at)
                VALUES (:module_id, :question_text, :explanation, :difficulty, :status, NOW(), NOW())");
            $stmt->execute([
                ':module_id' => $moduleId,
                ':question_text' => trim($data['question_text']),
                ':explanation' => trim($data['explanation'] ?? ''),
                ':difficulty' => $difficulty,
                ':status' => $status,
            ]);
            $questionId = (int)$pdo->lastInsertId();

            $stmtOption = $pdo->prepare("INSERT INTO question_options
                (question_id, option_label, option_text, is_correct, created_at, updated_at)
                VALUES (:question_id, :option_label, :option_text, :is_correct, NOW(), NOW())");
            foreach ($labels as $label) {
                $stmtOption->execute([
                    ':question_id' => $questionId,
                    ':option_label' => $label,
                    ':option_text' => trim($data['option_' . $label]),
                    ':is_correct' => $correctOption === $label ? 1 : 0,
                ]);
            }
            $pdo->commit();

            redirect(current_url('preguntas_modulo', ['module_id' => (string)$moduleId, 'success' => 'pregunta']));
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $module = $module ?? ['id' => $moduleId, 'title' => 'Módulo', 'course_title' => 'Curso'];
            view('pregunta_form', ['title' => 'Crear pregunta', 'module' => $module, 'errors' => ['general' => $e->getMessage()], 'old' => $data]);
        }
    }

    public function myTrainings(): void
    {
        view('mis_capacitaciones', [
            'title' => 'Mis capacitaciones',
            'person' => null,
            'modules' => [],
            'error' => null,
            'document' => $_GET['document'] ?? '',
        ]);
    }

    public function myTrainingsResult(): void
    {
        $document = normalize_document($_POST['document_number'] ?? $_GET['document_number'] ?? '');
        $person = null;
        $modules = [];
        $error = null;

        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida.');
            }

            $pdo = Database::connection();
            $person = $this->service->findPersonByDocument($pdo, $document);
            if (!$person) {
                throw new RuntimeException('La cédula no está registrada o la persona está inactiva. Primero debe realizar el registro inicial.');
            }
            $modules = $this->service->getAssignedModules($pdo, $person);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('mis_capacitaciones', [
            'title' => 'Mis capacitaciones',
            'person' => $person,
            'modules' => $modules,
            'error' => $error,
            'document' => $document,
        ]);
    }

    public function startExam(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        $moduleId = (int)($_POST['module_id'] ?? 0);

        try {
            if ($document === '' || $moduleId <= 0) {
                throw new RuntimeException('Debe indicar cédula y módulo para iniciar el examen.');
            }

            $pdo = Database::connection();
            $person = $this->service->findPersonByDocument($pdo, $document);
            if (!$person) {
                throw new RuntimeException('La persona no está registrada o está inactiva.');
            }

            $attemptId = $this->service->createAttempt($pdo, $person, $moduleId);
            redirect(current_url('presentar_examen', ['attempt_id' => (string)$attemptId]));
        } catch (Throwable $e) {
            $person = null;
            $modules = [];
            try {
                if ($document !== '') {
                    $pdo = $pdo ?? Database::connection();
                    $person = $this->service->findPersonByDocument($pdo, $document);
                    $modules = $person ? $this->service->getAssignedModules($pdo, $person) : [];
                }
            } catch (Throwable) {
                // Mantener el error original.
            }
            view('mis_capacitaciones', [
                'title' => 'Mis capacitaciones',
                'person' => $person,
                'modules' => $modules,
                'error' => $e->getMessage(),
                'document' => $document,
            ]);
        }
    }

    public function takeExam(): void
    {
        $attemptId = (int)($_GET['attempt_id'] ?? 0);
        if ($attemptId <= 0) {
            redirect(current_url('mis_capacitaciones'));
        }

        try {
            $pdo = Database::connection();
            $attempt = $this->service->getAttempt($pdo, $attemptId);
            if (!$attempt) {
                throw new RuntimeException('Intento de examen no encontrado.');
            }

            if ($attempt['status'] !== 'en_progreso') {
                redirect(current_url('resultado_examen', ['attempt_id' => (string)$attemptId]));
            }

            $this->service->expireOverdueCurrentQuestion($pdo, $attemptId);
            $finished = $this->service->finalizeAttemptIfComplete($pdo, $attemptId);
            if ($finished && $finished['status'] !== 'en_progreso') {
                redirect(current_url('resultado_examen', ['attempt_id' => (string)$attemptId]));
            }

            $currentQuestion = $this->service->getCurrentQuestion($pdo, $attemptId);
            if (!$currentQuestion) {
                $this->service->finalizeAttemptIfComplete($pdo, $attemptId);
                redirect(current_url('resultado_examen', ['attempt_id' => (string)$attemptId]));
            }

            $expiresAt = strtotime((string)$currentQuestion['expires_at']);
            $secondsRemaining = max(0, $expiresAt - time());
            if ($secondsRemaining <= 0) {
                $this->service->expireOverdueCurrentQuestion($pdo, $attemptId);
                redirect(current_url('presentar_examen', ['attempt_id' => (string)$attemptId]));
            }

            $options = $this->service->getQuestionOptions($pdo, (int)$currentQuestion['question_id']);
            $progress = $this->service->getAttemptProgress($pdo, $attemptId);

            view('examen_pregunta', [
                'title' => 'Presentar examen',
                'attempt' => $attempt,
                'question' => $currentQuestion,
                'options' => $options,
                'progress' => $progress,
                'secondsRemaining' => $secondsRemaining,
            ]);
        } catch (Throwable $e) {
            view('mis_capacitaciones', [
                'title' => 'Mis capacitaciones',
                'person' => null,
                'modules' => [],
                'error' => $e->getMessage(),
                'document' => '',
            ]);
        }
    }

    public function submitAnswer(): void
    {
        $attemptId = (int)($_POST['attempt_id'] ?? 0);
        $attemptQuestionId = (int)($_POST['attempt_question_id'] ?? 0);
        $selectedOptionId = isset($_POST['selected_option_id']) ? (int)$_POST['selected_option_id'] : null;

        if ($attemptId <= 0 || $attemptQuestionId <= 0) {
            redirect(current_url('mis_capacitaciones'));
        }

        try {
            $pdo = Database::connection();
            $this->service->saveAnswer($pdo, $attemptId, $attemptQuestionId, $selectedOptionId ?: null);
            $this->service->finalizeAttemptIfComplete($pdo, $attemptId);
        } catch (Throwable) {
            // En fase 3 se evita mostrar errores técnicos durante el examen y se continúa el flujo seguro.
        }

        redirect(current_url('presentar_examen', ['attempt_id' => (string)$attemptId]));
    }

    public function result(): void
    {
        $attemptId = (int)($_GET['attempt_id'] ?? 0);
        if ($attemptId <= 0) {
            redirect(current_url('mis_capacitaciones'));
        }

        try {
            $pdo = Database::connection();
            $this->service->expireOverdueCurrentQuestion($pdo, $attemptId);
            $attempt = $this->service->finalizeAttemptIfComplete($pdo, $attemptId) ?: $this->service->getAttempt($pdo, $attemptId);
            if (!$attempt) {
                throw new RuntimeException('Intento no encontrado.');
            }
            if ($attempt['status'] === 'en_progreso') {
                redirect(current_url('presentar_examen', ['attempt_id' => (string)$attemptId]));
            }
            $review = $this->service->getAttemptReview($pdo, $attemptId);
            $certificate = null;
            $certificateError = null;
            $reward = null;
            $rewardError = null;
            if ((int)$attempt['passed'] === 1) {
                try {
                    $certificateService = new CertificateService();
                    $certificate = $certificateService->ensureCertificateForPassedAttempt($pdo, $attemptId);
                } catch (Throwable $certificateException) {
                    $certificateError = $certificateException->getMessage();
                }
                try {
                    $rewardService = new RewardService();
                    $reward = $rewardService->ensureRewardForAttempt($pdo, $attemptId);
                } catch (Throwable $rewardException) {
                    $rewardError = $rewardException->getMessage();
                }
            }

            view('examen_resultado', [
                'title' => 'Resultado del examen',
                'attempt' => $attempt,
                'review' => $review,
                'passingScore' => ExamService::PASSING_SCORE,
                'certificate' => $certificate,
                'certificateError' => $certificateError,
                'reward' => $reward,
                'rewardError' => $rewardError,
            ]);
        } catch (Throwable $e) {
            view('mis_capacitaciones', [
                'title' => 'Mis capacitaciones',
                'person' => null,
                'modules' => [],
                'error' => $e->getMessage(),
                'document' => '',
            ]);
        }
    }
}
