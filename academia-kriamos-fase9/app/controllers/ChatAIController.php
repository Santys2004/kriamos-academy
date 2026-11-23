<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../services/ChatAIService.php';

class ChatAIController
{
    private ChatAIService $service;

    public function __construct()
    {
        $this->service = new ChatAIService();
    }

    public function form(): void
    {
        $token = $_GET['session'] ?? '';
        $session = null;
        $messages = [];
        $error = null;

        if ($token !== '') {
            try {
                $pdo = Database::connection();
                $session = $this->service->getSessionByToken($pdo, $token);
                if (!$session) {
                    throw new RuntimeException('La sesión de chat no fue encontrada.');
                }
                $messages = $this->service->getMessages($pdo, (int)$session['id']);
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }

        view('chat_ia', [
            'title' => 'Asistente KRIAMOS IA',
            'session' => $session,
            'messages' => $messages,
            'error' => $error,
            'document' => $_GET['document'] ?? '',
        ]);
    }

    public function start(): void
    {
        $document = normalize_document($_POST['document_number'] ?? '');
        $error = null;
        $session = null;
        $messages = [];

        try {
            if ($document === '') {
                throw new RuntimeException('Ingrese una cédula válida para iniciar el chat.');
            }

            $pdo = Database::connection();
            $person = $this->service->findPersonByDocument($pdo, $document);
            if (!$person) {
                throw new RuntimeException('La cédula no está registrada o la persona está inactiva. Primero debe realizar el registro inicial.');
            }

            $session = $this->service->startSession($pdo, (int)$person['id'], 'web');
            $messages = $this->service->getMessages($pdo, (int)$session['id']);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('chat_ia', [
            'title' => 'Asistente KRIAMOS IA',
            'session' => $session,
            'messages' => $messages,
            'error' => $error,
            'document' => $document,
        ]);
    }

    public function ask(): void
    {
        $token = $_POST['session_token'] ?? '';
        $question = $_POST['question'] ?? '';
        $session = null;
        $messages = [];
        $error = null;

        try {
            if ($token === '') {
                throw new RuntimeException('La sesión de chat no es válida.');
            }

            $pdo = Database::connection();
            $payload = $this->service->ask($pdo, $token, $question);
            $session = $payload['session'];
            $messages = $payload['messages'];
        } catch (Throwable $e) {
            $error = $e->getMessage();
            try {
                $pdo = Database::connection();
                $session = $this->service->getSessionByToken($pdo, $token);
                if ($session) {
                    $messages = $this->service->getMessages($pdo, (int)$session['id']);
                }
            } catch (Throwable) {
                // Mantener error original.
            }
        }

        view('chat_ia', [
            'title' => 'Asistente KRIAMOS IA',
            'session' => $session,
            'messages' => $messages,
            'error' => $error,
            'document' => '',
        ]);
    }

    public function admin(): void
    {
        $error = null;
        $success = $_GET['success'] ?? null;
        $knowledge = [];
        $sessions = [];

        try {
            $pdo = Database::connection();
            $knowledge = $this->service->listKnowledge($pdo);
            $sessions = $this->service->listRecentSessions($pdo);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }

        view('admin_chat_ia', [
            'title' => 'Administración Chat IA',
            'knowledge' => $knowledge,
            'sessions' => $sessions,
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function storeKnowledge(): void
    {
        try {
            $pdo = Database::connection();
            $this->service->createKnowledge($pdo, $_POST);
            redirect(current_url('admin_chat_ia', ['success' => 'Conocimiento agregado correctamente.']));
        } catch (Throwable $e) {
            view('admin_chat_ia', [
                'title' => 'Administración Chat IA',
                'knowledge' => [],
                'sessions' => [],
                'error' => $e->getMessage(),
                'success' => null,
            ]);
        }
    }

    public function toggleKnowledge(): void
    {
        try {
            $id = (int)($_POST['knowledge_id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('Registro de conocimiento no válido.');
            }
            $pdo = Database::connection();
            $this->service->toggleKnowledge($pdo, $id);
            redirect(current_url('admin_chat_ia', ['success' => 'Estado actualizado.']));
        } catch (Throwable $e) {
            view('admin_chat_ia', [
                'title' => 'Administración Chat IA',
                'knowledge' => [],
                'sessions' => [],
                'error' => $e->getMessage(),
                'success' => null,
            ]);
        }
    }
}
