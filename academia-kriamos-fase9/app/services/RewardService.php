<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class RewardService
{
    public function ensureRewardForAttempt(PDO $pdo, int $attemptId): ?array
    {
        $attempt = $this->getRewardableAttempt($pdo, $attemptId);
        if (!$attempt) {
            return null;
        }

        $existing = $this->findByAttemptId($pdo, $attemptId);
        if ($existing) {
            return $this->appendDerivedFields($existing);
        }

        $rule = $this->findRuleForScore($pdo, (float)$attempt['score'], (string)$attempt['person_type']);
        if (!$rule) {
            return null;
        }

        $stmt = $pdo->prepare("INSERT INTO rewards
            (person_id, module_id, course_id, exam_attempt_id, reward_rule_id, score, frozen_chickens_qty, reward_description, status, earned_at, created_at, updated_at)
            VALUES (:person_id, :module_id, :course_id, :exam_attempt_id, :reward_rule_id, :score, :frozen_chickens_qty, :reward_description, 'pendiente', NOW(), NOW(), NOW())");
        $stmt->execute([
            ':person_id' => $attempt['person_id'],
            ':module_id' => $attempt['module_id'],
            ':course_id' => $attempt['course_id'],
            ':exam_attempt_id' => $attemptId,
            ':reward_rule_id' => $rule['id'],
            ':score' => $attempt['score'],
            ':frozen_chickens_qty' => $rule['frozen_chickens_qty'],
            ':reward_description' => $rule['reward_description'],
        ]);

        return $this->findById($pdo, (int)$pdo->lastInsertId());
    }

    public function getRewardableAttempt(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare("SELECT
                ea.*,
                p.full_name,
                p.document_number,
                p.person_type,
                p.company,
                p.work_site,
                p.department,
                p.position,
                m.title AS module_title,
                m.course_id,
                c.title AS course_title,
                c.code AS course_code
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            INNER JOIN course_modules m ON m.id = ea.module_id
            INNER JOIN courses c ON c.id = m.course_id
            WHERE ea.id = :id
              AND ea.status = 'aprobado'
              AND ea.passed = 1
              AND p.person_type = 'empleado'
              AND ea.score >= 90
            LIMIT 1");
        $stmt->execute([':id' => $attemptId]);
        return $stmt->fetch() ?: null;
    }

    public function findRuleForScore(PDO $pdo, float $score, string $personType): ?array
    {
        $stmt = $pdo->prepare("SELECT *
            FROM reward_rules
            WHERE active = 1
              AND :score BETWEEN min_score AND max_score
              AND (only_employees = 0 OR :person_type = 'empleado')
            ORDER BY frozen_chickens_qty DESC, min_score DESC
            LIMIT 1");
        $stmt->execute([
            ':score' => $score,
            ':person_type' => $personType,
        ]);
        return $stmt->fetch() ?: null;
    }

    public function findByAttemptId(PDO $pdo, int $attemptId): ?array
    {
        $stmt = $pdo->prepare($this->rewardSelectSql() . ' WHERE r.exam_attempt_id = :attempt_id LIMIT 1');
        $stmt->execute([':attempt_id' => $attemptId]);
        $row = $stmt->fetch() ?: null;
        return $row ? $this->appendDerivedFields($row) : null;
    }

    public function findById(PDO $pdo, int $rewardId): ?array
    {
        $stmt = $pdo->prepare($this->rewardSelectSql() . ' WHERE r.id = :id LIMIT 1');
        $stmt->execute([':id' => $rewardId]);
        $row = $stmt->fetch() ?: null;
        return $row ? $this->appendDerivedFields($row) : null;
    }

    public function listRewards(PDO $pdo, array $filters = []): array
    {
        [$whereSql, $params] = $this->buildFilterWhere($filters);
        $stmt = $pdo->prepare($this->rewardSelectSql() . $whereSql . ' ORDER BY r.earned_at DESC, r.id DESC LIMIT 500');
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->appendDerivedFields($row), $stmt->fetchAll());
    }

    public function listByDocument(PDO $pdo, string $document): array
    {
        $stmt = $pdo->prepare($this->rewardSelectSql() . ' WHERE p.document_number = :document ORDER BY r.earned_at DESC, r.id DESC');
        $stmt->execute([':document' => $document]);
        return array_map(fn(array $row) => $this->appendDerivedFields($row), $stmt->fetchAll());
    }

    public function stats(PDO $pdo, array $filters = []): array
    {
        [$whereSql, $params] = $this->buildFilterWhere($filters);
        $stmt = $pdo->prepare("SELECT
                COUNT(*) AS total_rewards,
                SUM(CASE WHEN r.status = 'pendiente' THEN 1 ELSE 0 END) AS pending_rewards,
                SUM(CASE WHEN r.status = 'entregada' THEN 1 ELSE 0 END) AS delivered_rewards,
                SUM(CASE WHEN r.status = 'anulada' THEN 1 ELSE 0 END) AS void_rewards,
                COALESCE(SUM(CASE WHEN r.status <> 'anulada' THEN r.frozen_chickens_qty ELSE 0 END), 0) AS total_chickens,
                COALESCE(SUM(CASE WHEN r.status = 'pendiente' THEN r.frozen_chickens_qty ELSE 0 END), 0) AS pending_chickens,
                COALESCE(SUM(CASE WHEN r.status = 'entregada' THEN r.frozen_chickens_qty ELSE 0 END), 0) AS delivered_chickens
            FROM rewards r
            INNER JOIN people p ON p.id = r.person_id
            INNER JOIN course_modules m ON m.id = r.module_id
            INNER JOIN courses c ON c.id = r.course_id
            INNER JOIN reward_rules rr ON rr.id = r.reward_rule_id" . $whereSql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];
        return [
            'total_rewards' => (int)($row['total_rewards'] ?? 0),
            'pending_rewards' => (int)($row['pending_rewards'] ?? 0),
            'delivered_rewards' => (int)($row['delivered_rewards'] ?? 0),
            'void_rewards' => (int)($row['void_rewards'] ?? 0),
            'total_chickens' => (int)($row['total_chickens'] ?? 0),
            'pending_chickens' => (int)($row['pending_chickens'] ?? 0),
            'delivered_chickens' => (int)($row['delivered_chickens'] ?? 0),
        ];
    }

    public function markDelivered(PDO $pdo, int $rewardId, string $deliveredBy, string $notes = ''): void
    {
        $stmt = $pdo->prepare("UPDATE rewards
            SET status = 'entregada', delivered_at = NOW(), delivered_by = :delivered_by, delivery_notes = :notes, updated_at = NOW()
            WHERE id = :id AND status = 'pendiente'");
        $stmt->execute([
            ':id' => $rewardId,
            ':delivered_by' => trim($deliveredBy),
            ':notes' => trim($notes),
        ]);
    }

    public function voidReward(PDO $pdo, int $rewardId, string $deliveredBy, string $notes = ''): void
    {
        $stmt = $pdo->prepare("UPDATE rewards
            SET status = 'anulada', delivered_at = NULL, delivered_by = :delivered_by, delivery_notes = :notes, updated_at = NOW()
            WHERE id = :id AND status <> 'anulada'");
        $stmt->execute([
            ':id' => $rewardId,
            ':delivered_by' => trim($deliveredBy),
            ':notes' => trim($notes),
        ]);
    }

    public function exportCsv(PDO $pdo, array $filters = []): string
    {
        $rows = $this->listRewards($pdo, $filters);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [
            'ID', 'Estado', 'Fecha ganada', 'Fecha entrega', 'Nombre', 'Cedula', 'Sede', 'Equipo', 'Cargo',
            'Curso', 'Modulo', 'Nota', 'Pollos congelados', 'Descripcion recompensa', 'Entregado por', 'Observaciones'
        ]);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['id'],
                $row['status'],
                $row['earned_at'],
                $row['delivered_at'],
                $row['full_name'],
                $row['document_number'],
                $row['work_site'],
                $row['department'],
                $row['position'],
                $row['course_title'],
                $row['module_title'],
                $row['score'],
                $row['frozen_chickens_qty'],
                $row['reward_description'],
                $row['delivered_by'],
                $row['delivery_notes'],
            ]);
        }
        rewind($handle);
        return stream_get_contents($handle) ?: '';
    }

    public function filtersFromRequest(array $request): array
    {
        return [
            'status' => in_array(($request['status'] ?? ''), ['pendiente','entregada','anulada'], true) ? $request['status'] : '',
            'work_site' => in_array(($request['work_site'] ?? ''), ['GAEE','GALM','Oficina'], true) ? $request['work_site'] : '',
            'department' => in_array(($request['department'] ?? ''), ['Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos'], true) ? $request['department'] : '',
            'date_from' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($request['date_from'] ?? '')) ? $request['date_from'] : '',
            'date_to' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($request['date_to'] ?? '')) ? $request['date_to'] : '',
        ];
    }

    private function buildFilterWhere(array $filters): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'r.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['work_site'])) {
            $where[] = 'p.work_site = :work_site';
            $params[':work_site'] = $filters['work_site'];
        }
        if (!empty($filters['department'])) {
            $where[] = 'p.department = :department';
            $params[':department'] = $filters['department'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(r.earned_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(r.earned_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function rewardSelectSql(): string
    {
        return "SELECT
                r.*,
                rr.name AS reward_rule_name,
                p.full_name,
                p.document_number,
                p.person_type,
                p.company,
                p.work_site,
                p.department,
                p.position,
                p.email,
                p.cellphone,
                m.title AS module_title,
                c.title AS course_title,
                c.code AS course_code
            FROM rewards r
            INNER JOIN reward_rules rr ON rr.id = r.reward_rule_id
            INNER JOIN people p ON p.id = r.person_id
            INNER JOIN course_modules m ON m.id = r.module_id
            INNER JOIN courses c ON c.id = r.course_id";
    }

    private function appendDerivedFields(array $row): array
    {
        $row['status_label'] = match ($row['status'] ?? '') {
            'pendiente' => 'Pendiente de entrega',
            'entregada' => 'Entregada',
            'anulada' => 'Anulada',
            default => 'Sin estado',
        };
        $row['status_badge'] = match ($row['status'] ?? '') {
            'pendiente' => 'text-bg-warning',
            'entregada' => 'text-bg-success',
            'anulada' => 'text-bg-secondary',
            default => 'text-bg-light',
        };
        return $row;
    }
}
