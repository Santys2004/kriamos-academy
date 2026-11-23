<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class ChatAIService
{
    public function findPersonByDocument(PDO $pdo, string $document): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM people WHERE document_number = :document AND status = "activo" LIMIT 1');
        $stmt->execute(['document' => $document]);
        $person = $stmt->fetch();
        return $person ?: null;
    }

    public function startSession(PDO $pdo, int $personId, string $channel = 'web'): array
    {
        $token = bin2hex(random_bytes(24));
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare('INSERT INTO ai_chat_sessions (person_id, session_token, channel, status, started_at, last_interaction_at) VALUES (:person_id, :token, :channel, "abierta", :now, :now)');
        $stmt->execute([
            'person_id' => $personId,
            'token' => $token,
            'channel' => $channel,
            'now' => $now,
        ]);

        $session = $this->getSessionByToken($pdo, $token);
        if (!$session) {
            throw new RuntimeException('No fue posible iniciar el chat.');
        }

        $welcome = 'Hola, soy el Asistente KRIAMOS IA. Estoy disponible para orientar sobre uso de la aplicación, capacitaciones, certificados, bioseguridad, bienestar animal y procedimientos internos cargados en la base de conocimiento. Para decisiones sanitarias, medicamentos, mortalidad alta o emergencias, siempre se debe escalar al responsable técnico.';
        $this->insertMessage($pdo, (int)$session['id'], 'assistant', $welcome, null, null);

        return $this->getSessionByToken($pdo, $token) ?: $session;
    }

    public function getSessionByToken(PDO $pdo, string $token): ?array
    {
        $stmt = $pdo->prepare('SELECT s.*, p.full_name, p.document_number, p.person_type, p.work_site, p.company, p.position, p.department, p.photo_path
            FROM ai_chat_sessions s
            INNER JOIN people p ON p.id = s.person_id
            WHERE s.session_token = :token
            LIMIT 1');
        $stmt->execute(['token' => $token]);
        $session = $stmt->fetch();
        return $session ?: null;
    }

    public function getMessages(PDO $pdo, int $sessionId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM ai_chat_messages WHERE session_id = :session_id ORDER BY created_at ASC, id ASC');
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function ask(PDO $pdo, string $sessionToken, string $question): array
    {
        $question = trim($question);
        if ($question === '') {
            throw new RuntimeException('Escriba una pregunta para continuar.');
        }
        if (mb_strlen($question) > 1200) {
            throw new RuntimeException('La pregunta es demasiado extensa. Use máximo 1.200 caracteres.');
        }

        $session = $this->getSessionByToken($pdo, $sessionToken);
        if (!$session || $session['status'] !== 'abierta') {
            throw new RuntimeException('La sesión de chat no existe o se encuentra cerrada.');
        }

        $sessionId = (int)$session['id'];
        $this->insertMessage($pdo, $sessionId, 'user', $question, null, null);

        $knowledgeMatches = $this->searchKnowledge($pdo, $question, $session);
        $answerPayload = $this->buildLocalAnswer($question, $knowledgeMatches, $session);

        $this->insertMessage(
            $pdo,
            $sessionId,
            'assistant',
            $answerPayload['answer'],
            implode(',', $answerPayload['matched_ids']),
            $answerPayload['confidence']
        );

        $stmt = $pdo->prepare('UPDATE ai_chat_sessions SET last_interaction_at = :now WHERE id = :id');
        $stmt->execute(['now' => date('Y-m-d H:i:s'), 'id' => $sessionId]);

        return [
            'session' => $this->getSessionByToken($pdo, $sessionToken),
            'messages' => $this->getMessages($pdo, $sessionId),
            'matches' => $knowledgeMatches,
        ];
    }

    private function insertMessage(PDO $pdo, int $sessionId, string $sender, string $message, ?string $matchedIds, ?float $confidence): void
    {
        $stmt = $pdo->prepare('INSERT INTO ai_chat_messages (session_id, sender, message, matched_knowledge_ids, confidence_score, created_at) VALUES (:session_id, :sender, :message, :matched_ids, :confidence, :created_at)');
        $stmt->execute([
            'session_id' => $sessionId,
            'sender' => $sender,
            'message' => $message,
            'matched_ids' => $matchedIds,
            'confidence' => $confidence,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function searchKnowledge(PDO $pdo, string $question, array $personOrSession): array
    {
        $stmt = $pdo->prepare('SELECT * FROM ai_knowledge_base
            WHERE status = "activo"
              AND (target_audience = "todos" OR target_audience = :person_type)
              AND (work_site = "todos" OR work_site = :work_site)
              AND (department = "todos" OR department = :department)
            ORDER BY updated_at DESC, id DESC');
        $stmt->execute([
            'person_type' => $personOrSession['person_type'] ?? 'empleado',
            'work_site' => $personOrSession['work_site'] ?? 'GAEE',
            'department' => $personOrSession['department'] ?: 'todos',
        ]);

        $rows = $stmt->fetchAll();
        $questionTokens = $this->tokenize($question);
        $scored = [];
        foreach ($rows as $row) {
            $haystackTitle = $this->tokenize($row['title'] ?? '');
            $haystackKeywords = $this->tokenize($row['keywords'] ?? '');
            $haystackContent = $this->tokenize($row['content'] ?? '');

            $score = 0.0;
            foreach ($questionTokens as $token) {
                if (in_array($token, $haystackTitle, true)) {
                    $score += 3.0;
                }
                if (in_array($token, $haystackKeywords, true)) {
                    $score += 2.2;
                }
                if (in_array($token, $haystackContent, true)) {
                    $score += 1.0;
                }
            }

            $exactNeedle = $this->normalizeText($question);
            if ($exactNeedle !== '' && str_contains($this->normalizeText((string)$row['title']), $exactNeedle)) {
                $score += 4.0;
            }

            if ($score > 0) {
                $row['score'] = round($score, 2);
                $scored[] = $row;
            }
        }

        usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        $max = (int)(ai_config('max_context_items') ?: 3);
        return array_slice($scored, 0, $max);
    }

    private function buildLocalAnswer(string $question, array $matches, array $session): array
    {
        $minConfidence = (float)(ai_config('min_confidence') ?: 1.5);
        $confidence = !empty($matches) ? (float)$matches[0]['score'] : 0.0;
        $matchedIds = array_map(static fn(array $m): string => (string)$m['id'], $matches);

        if (empty($matches) || $confidence < $minConfidence) {
            $answer = 'No encontré una respuesta suficientemente confiable en la base de conocimiento interna de KRIAMOS. Recomiendo consultar al administrador de capacitación, supervisor, administrador de granja o Dirección de Operaciones para evitar aplicar una instrucción incorrecta.';
            return ['answer' => $answer, 'matched_ids' => [], 'confidence' => $confidence];
        }

        $parts = [];
        $parts[] = 'Con base en la información interna cargada en Academia KRIAMOS:';
        foreach ($matches as $index => $match) {
            $content = $this->shorten((string)$match['content'], $index === 0 ? 700 : 360);
            $parts[] = '• ' . $match['title'] . ': ' . $content;
        }

        if ($this->containsSensitiveKeyword($question)) {
            $parts[] = 'Nota crítica: esta orientación no reemplaza la decisión del Médico Veterinario, Administrador de Granja, Dirección de Operaciones, SG-SST o autoridad sanitaria competente. Si se trata de sospecha sanitaria, mortalidad alta, medicamento, sacrificio humanitario o emergencia, escale el caso inmediatamente.';
        } else {
            $parts[] = 'Para mayor seguridad, verifique siempre el POE vigente, el módulo asignado y las instrucciones del responsable directo.';
        }

        return [
            'answer' => implode("\n\n", $parts),
            'matched_ids' => $matchedIds,
            'confidence' => $confidence,
        ];
    }

    private function containsSensitiveKeyword(string $question): bool
    {
        $text = $this->normalizeText($question);
        foreach ((array)ai_config('sensitive_keywords') as $keyword) {
            if (str_contains($text, $this->normalizeText((string)$keyword))) {
                return true;
            }
        }
        return false;
    }

    private function tokenize(string $text): array
    {
        $text = $this->normalizeText($text);
        $parts = preg_split('/\s+/', $text) ?: [];
        $stopwords = ['que','para','con','los','las','una','uno','del','por','como','debe','deben','esta','este','sobre','cual','cuando','donde','tiene','tengo','hacer','puedo','puede','de','la','el','y','o','en','a','un','se','al','es'];
        $tokens = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if (mb_strlen($part) < 3 || in_array($part, $stopwords, true)) {
                continue;
            }
            $tokens[] = $part;
        }
        return array_values(array_unique($tokens));
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ]);
        $text = preg_replace('/[^a-z0-9\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        return trim($text);
    }

    private function shorten(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit - 3) . '...';
    }

    public function createKnowledge(PDO $pdo, array $data): void
    {
        $required = validate_required($data, [
            'title' => 'título',
            'content' => 'contenido',
            'source_type' => 'tipo de fuente',
            'target_audience' => 'audiencia',
            'work_site' => 'sede',
            'department' => 'equipo',
        ]);
        if ($required) {
            throw new RuntimeException(implode(' ', $required));
        }

        $stmt = $pdo->prepare('INSERT INTO ai_knowledge_base (title, content, keywords, source_type, target_audience, work_site, department, status, created_by, created_at, updated_at)
            VALUES (:title, :content, :keywords, :source_type, :target_audience, :work_site, :department, "activo", :created_by, :now, :now)');
        $stmt->execute([
            'title' => trim((string)$data['title']),
            'content' => trim((string)$data['content']),
            'keywords' => trim((string)($data['keywords'] ?? '')),
            'source_type' => $data['source_type'],
            'target_audience' => $data['target_audience'],
            'work_site' => $data['work_site'],
            'department' => $data['department'],
            'created_by' => trim((string)($data['created_by'] ?? 'Administrador')),
            'now' => date('Y-m-d H:i:s'),
        ]);
    }

    public function listKnowledge(PDO $pdo): array
    {
        return $pdo->query('SELECT * FROM ai_knowledge_base ORDER BY status ASC, updated_at DESC, id DESC')->fetchAll();
    }

    public function listRecentSessions(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT s.*, p.full_name, p.document_number, p.person_type, p.work_site, p.company, p.position,
            (SELECT COUNT(*) FROM ai_chat_messages m WHERE m.session_id = s.id) AS total_messages
            FROM ai_chat_sessions s
            INNER JOIN people p ON p.id = s.person_id
            ORDER BY s.last_interaction_at DESC
            LIMIT 50');
        return $stmt->fetchAll();
    }

    public function toggleKnowledge(PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare('UPDATE ai_knowledge_base SET status = IF(status = "activo", "inactivo", "activo"), updated_at = :now WHERE id = :id');
        $stmt->execute(['id' => $id, 'now' => date('Y-m-d H:i:s')]);
    }
}
