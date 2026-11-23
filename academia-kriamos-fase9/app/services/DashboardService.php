<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';

class DashboardService
{
    public const EMPLOYEE_TARGET_PERCENT = 95.0;

    public function filtersFromRequest(array $source): array
    {
        $filters = [
            'work_site' => $source['work_site'] ?? '',
            'department' => $source['department'] ?? '',
            'person_type' => $source['person_type'] ?? '',
            'date_from' => $source['date_from'] ?? '',
            'date_to' => $source['date_to'] ?? '',
        ];

        $validSites = ['', 'GAEE', 'GALM', 'Oficina'];
        $validDepartments = ['', 'Operaciones', 'Mantenimiento', 'Compras', 'Gestión Humana', 'Administrativos'];
        $validTypes = ['', 'empleado', 'contratista'];

        if (!in_array($filters['work_site'], $validSites, true)) {
            $filters['work_site'] = '';
        }
        if (!in_array($filters['department'], $validDepartments, true)) {
            $filters['department'] = '';
        }
        if (!in_array($filters['person_type'], $validTypes, true)) {
            $filters['person_type'] = '';
        }
        if (!$this->isDate($filters['date_from'])) {
            $filters['date_from'] = '';
        }
        if (!$this->isDate($filters['date_to'])) {
            $filters['date_to'] = '';
        }

        return $filters;
    }

    public function getDashboard(PDO $pdo, array $filters): array
    {
        return [
            'target_percent' => self::EMPLOYEE_TARGET_PERCENT,
            'overview' => $this->overview($pdo, $filters),
            'completion' => $this->completion($pdo, $filters),
            'by_site' => $this->completionBySite($pdo, $filters),
            'by_department' => $this->completionByDepartment($pdo, $filters),
            'approval_by_site' => $this->approvalBySite($pdo, $filters),
            'approval_by_department' => $this->approvalByDepartment($pdo, $filters),
            'skills' => $this->skills($pdo, $filters),
            'courses' => $this->courses($pdo, $filters),
            'time' => $this->timeDedicated($pdo, $filters),
            'pending_people' => $this->pendingPeople($pdo, $filters),
        ];
    }

    public function exportCsv(PDO $pdo, array $filters): string
    {
        $dashboard = $this->getDashboard($pdo, $filters);
        $lines = [];
        $lines[] = ['Indicador', 'Valor'];
        $lines[] = ['Meta empleados capacitados', self::EMPLOYEE_TARGET_PERCENT . '%'];
        $lines[] = ['Cumplimiento empleados', $dashboard['overview']['employee_coverage_percent'] . '%'];
        $lines[] = ['Brecha frente a meta', $dashboard['overview']['employee_gap_percent'] . '%'];
        $lines[] = ['Completitud general por módulos asignados', $dashboard['completion']['completion_percent'] . '%'];
        $lines[] = ['Tasa de aprobación general', $dashboard['overview']['approval_rate_percent'] . '%'];
        $lines[] = ['Tiempo total dedicado', $dashboard['time']['total_hours'] . ' horas'];
        $lines[] = [];
        $lines[] = ['Cumplimiento por granja', ''];
        $lines[] = ['Sitio', 'Personas', 'Personas con certificado vigente', 'Cobertura %'];
        foreach ($dashboard['by_site'] as $row) {
            $lines[] = [$row['work_site'], $row['people_total'], $row['people_certified'], $row['coverage_percent'] . '%'];
        }
        $lines[] = [];
        $lines[] = ['Adquisición de habilidades', ''];
        $lines[] = ['Habilidad', 'Módulos asignados', 'Módulos certificados', 'Adquisición %', 'Promedio'];
        foreach ($dashboard['skills'] as $row) {
            $lines[] = [$row['skill_name'], $row['assigned_total'], $row['completed_total'], $row['acquisition_percent'] . '%', $row['avg_score'] . '%'];
        }

        $fp = fopen('php://temp', 'rb+');
        foreach ($lines as $line) {
            fputcsv($fp, $line, ';');
        }
        rewind($fp);
        return (string)stream_get_contents($fp);
    }

    private function overview(PDO $pdo, array $filters): array
    {
        $personWhere = $this->personWhere($filters, 'p');
        $peopleSql = "SELECT
                COUNT(DISTINCT p.id) AS people_total,
                COUNT(DISTINCT CASE WHEN p.person_type = 'empleado' THEN p.id END) AS employee_total,
                COUNT(DISTINCT CASE WHEN p.person_type = 'contratista' THEN p.id END) AS contractor_total,
                COUNT(DISTINCT CASE WHEN p.person_type = 'empleado' AND cert.id IS NOT NULL THEN p.id END) AS employee_certified,
                COUNT(DISTINCT CASE WHEN p.person_type = 'contratista' AND cert.id IS NOT NULL THEN p.id END) AS contractor_certified,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN p.id END) AS people_certified
            FROM people p
            LEFT JOIN certificates cert ON cert.person_id = p.id AND cert.status = 'vigente' AND cert.expires_at >= NOW()
            WHERE p.status = 'activo' {$personWhere['sql']}";
        $stmt = $pdo->prepare($peopleSql);
        $stmt->execute($personWhere['params']);
        $people = $stmt->fetch() ?: [];

        $attemptWhere = $this->attemptWhere($filters, 'p', 'ea');
        $attemptSql = "SELECT
                COUNT(*) AS attempts_total,
                SUM(CASE WHEN ea.passed = 1 THEN 1 ELSE 0 END) AS attempts_passed,
                ROUND(AVG(CASE WHEN ea.status IN ('aprobado','reprobado') THEN ea.score ELSE NULL END), 2) AS avg_score
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            WHERE ea.status IN ('aprobado','reprobado') {$attemptWhere['sql']}";
        $stmt = $pdo->prepare($attemptSql);
        $stmt->execute($attemptWhere['params']);
        $attempts = $stmt->fetch() ?: [];

        $employeeTotal = (int)($people['employee_total'] ?? 0);
        $employeeCertified = (int)($people['employee_certified'] ?? 0);
        $employeeCoverage = $employeeTotal > 0 ? round(($employeeCertified / $employeeTotal) * 100, 2) : 0.0;
        $approvalTotal = (int)($attempts['attempts_total'] ?? 0);
        $approvalPassed = (int)($attempts['attempts_passed'] ?? 0);
        $approvalRate = $approvalTotal > 0 ? round(($approvalPassed / $approvalTotal) * 100, 2) : 0.0;

        return [
            'people_total' => (int)($people['people_total'] ?? 0),
            'employee_total' => $employeeTotal,
            'contractor_total' => (int)($people['contractor_total'] ?? 0),
            'people_certified' => (int)($people['people_certified'] ?? 0),
            'employee_certified' => $employeeCertified,
            'contractor_certified' => (int)($people['contractor_certified'] ?? 0),
            'employee_coverage_percent' => $employeeCoverage,
            'employee_gap_percent' => max(0.0, round(self::EMPLOYEE_TARGET_PERCENT - $employeeCoverage, 2)),
            'goal_reached' => $employeeCoverage >= self::EMPLOYEE_TARGET_PERCENT,
            'attempts_total' => $approvalTotal,
            'attempts_passed' => $approvalPassed,
            'approval_rate_percent' => $approvalRate,
            'avg_score' => round((float)($attempts['avg_score'] ?? 0), 2),
        ];
    }

    private function completion(PDO $pdo, array $filters): array
    {
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $sql = "SELECT
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total
            FROM ({$assignedSql}) a
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];
        $assigned = (int)($row['assigned_total'] ?? 0);
        $completed = (int)($row['completed_total'] ?? 0);
        return [
            'assigned_total' => $assigned,
            'completed_total' => $completed,
            'pending_total' => max(0, $assigned - $completed),
            'completion_percent' => $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0.0,
        ];
    }

    private function completionBySite(PDO $pdo, array $filters): array
    {
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $sql = "SELECT
                a.work_site,
                COUNT(DISTINCT a.person_id) AS people_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN a.person_id END) AS people_certified,
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total
            FROM ({$assignedSql}) a
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()
            GROUP BY a.work_site
            ORDER BY FIELD(a.work_site, 'GAEE', 'GALM', 'Oficina'), a.work_site";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'addCoverageFields'], $stmt->fetchAll());
    }

    private function completionByDepartment(PDO $pdo, array $filters): array
    {
        $filters['person_type'] = $filters['person_type'] === 'contratista' ? 'contratista' : $filters['person_type'];
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $sql = "SELECT
                COALESCE(a.department, 'Contratistas') AS department,
                COUNT(DISTINCT a.person_id) AS people_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN a.person_id END) AS people_certified,
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total
            FROM ({$assignedSql}) a
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()
            GROUP BY COALESCE(a.department, 'Contratistas')
            ORDER BY FIELD(COALESCE(a.department, 'Contratistas'), 'Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos','Contratistas'), department";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'addCoverageFields'], $stmt->fetchAll());
    }

    private function approvalBySite(PDO $pdo, array $filters): array
    {
        $where = $this->attemptWhere($filters, 'p', 'ea');
        $sql = "SELECT p.work_site,
                COUNT(*) AS attempts_total,
                SUM(CASE WHEN ea.passed = 1 THEN 1 ELSE 0 END) AS passed_total,
                ROUND(AVG(ea.score), 2) AS avg_score
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            WHERE ea.status IN ('aprobado','reprobado') {$where['sql']}
            GROUP BY p.work_site
            ORDER BY FIELD(p.work_site, 'GAEE', 'GALM', 'Oficina'), p.work_site";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($where['params']);
        return array_map([$this, 'addApprovalFields'], $stmt->fetchAll());
    }

    private function approvalByDepartment(PDO $pdo, array $filters): array
    {
        $where = $this->attemptWhere($filters, 'p', 'ea');
        $sql = "SELECT COALESCE(p.department, 'Contratistas') AS department,
                COUNT(*) AS attempts_total,
                SUM(CASE WHEN ea.passed = 1 THEN 1 ELSE 0 END) AS passed_total,
                ROUND(AVG(ea.score), 2) AS avg_score
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            WHERE ea.status IN ('aprobado','reprobado') {$where['sql']}
            GROUP BY COALESCE(p.department, 'Contratistas')
            ORDER BY FIELD(COALESCE(p.department, 'Contratistas'), 'Operaciones','Mantenimiento','Compras','Gestión Humana','Administrativos','Contratistas'), department";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($where['params']);
        return array_map([$this, 'addApprovalFields'], $stmt->fetchAll());
    }

    private function skills(PDO $pdo, array $filters): array
    {
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $skillLabel = "COALESCE(ms.skill_name,
            CASE
                WHEN a.course_title LIKE '%Bioseguridad%' THEN 'Bioseguridad'
                WHEN a.course_title LIKE '%Bienestar%' THEN 'Bienestar animal'
                WHEN a.course_title LIKE '%Mantenimiento%' OR a.module_title LIKE '%Fracttal%' THEN 'Mantenimiento y equipos críticos'
                WHEN a.course_title LIKE '%Emergencia%' OR a.module_title LIKE '%Emergencia%' THEN 'Respuesta ante emergencias'
                WHEN a.course_title LIKE '%Residuo%' OR a.module_title LIKE '%Residuo%' THEN 'Gestión ambiental y residuos'
                WHEN a.course_title LIKE '%SG-SST%' OR a.module_title LIKE '%seguridad%' THEN 'Trabajo seguro y SG-SST'
                ELSE 'Competencia operativa avícola'
            END)";
        $sql = "SELECT
                {$skillLabel} AS skill_name,
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total,
                ROUND(AVG(CASE WHEN ea.status IN ('aprobado','reprobado') THEN ea.score ELSE NULL END), 2) AS avg_score
            FROM ({$assignedSql}) a
            LEFT JOIN module_skills ms ON ms.module_id = a.module_id
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()
            LEFT JOIN exam_attempts ea ON ea.id = (
                SELECT ea2.id
                FROM exam_attempts ea2
                WHERE ea2.person_id = a.person_id AND ea2.module_id = a.module_id AND ea2.status IN ('aprobado','reprobado')
                ORDER BY ea2.started_at DESC, ea2.id DESC
                LIMIT 1
            )
            GROUP BY skill_name
            ORDER BY completed_total DESC, assigned_total DESC, skill_name
            LIMIT 12";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $assigned = (int)($row['assigned_total'] ?? 0);
            $completed = (int)($row['completed_total'] ?? 0);
            $row['assigned_total'] = $assigned;
            $row['completed_total'] = $completed;
            $row['pending_total'] = max(0, $assigned - $completed);
            $row['acquisition_percent'] = $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0.0;
            $row['avg_score'] = round((float)($row['avg_score'] ?? 0), 2);
        }
        unset($row);
        return $rows;
    }

    private function courses(PDO $pdo, array $filters): array
    {
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $sql = "SELECT
                a.course_code,
                a.course_title,
                COUNT(DISTINCT a.person_id) AS people_total,
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total,
                ROUND(AVG(CASE WHEN ea.status IN ('aprobado','reprobado') THEN ea.score ELSE NULL END), 2) AS avg_score
            FROM ({$assignedSql}) a
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()
            LEFT JOIN exam_attempts ea ON ea.id = (
                SELECT ea2.id
                FROM exam_attempts ea2
                WHERE ea2.person_id = a.person_id AND ea2.module_id = a.module_id AND ea2.status IN ('aprobado','reprobado')
                ORDER BY ea2.started_at DESC, ea2.id DESC
                LIMIT 1
            )
            GROUP BY a.course_code, a.course_title
            ORDER BY completed_total ASC, assigned_total DESC, a.course_code
            LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $assigned = (int)($row['assigned_total'] ?? 0);
            $completed = (int)($row['completed_total'] ?? 0);
            $row['assigned_total'] = $assigned;
            $row['completed_total'] = $completed;
            $row['pending_total'] = max(0, $assigned - $completed);
            $row['completion_percent'] = $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0.0;
            $row['avg_score'] = round((float)($row['avg_score'] ?? 0), 2);
        }
        unset($row);
        return $rows;
    }

    private function timeDedicated(PDO $pdo, array $filters): array
    {
        $where = $this->attemptWhere($filters, 'p', 'ea');
        $sql = "SELECT
                COUNT(*) AS finished_attempts,
                SUM(GREATEST(0, TIMESTAMPDIFF(SECOND, ea.started_at, ea.finished_at))) AS attempt_seconds,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, ea.started_at, ea.finished_at))) / 60, 2) AS avg_attempt_minutes
            FROM exam_attempts ea
            INNER JOIN people p ON p.id = ea.person_id
            WHERE ea.status IN ('aprobado','reprobado') AND ea.finished_at IS NOT NULL {$where['sql']}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($where['params']);
        $attempts = $stmt->fetch() ?: [];

        $logSeconds = 0;
        $logEvents = 0;
        try {
            $personWhere = $this->personWhere($filters, 'p');
            $dateSql = '';
            $params = $personWhere['params'];
            if ($filters['date_from'] !== '') {
                $dateSql .= ' AND lal.started_at >= :lal_date_from';
                $params[':lal_date_from'] = $filters['date_from'] . ' 00:00:00';
            }
            if ($filters['date_to'] !== '') {
                $dateSql .= ' AND lal.started_at <= :lal_date_to';
                $params[':lal_date_to'] = $filters['date_to'] . ' 23:59:59';
            }
            $stmt = $pdo->prepare("SELECT COUNT(*) AS events_total, SUM(duration_seconds) AS log_seconds
                FROM learning_activity_logs lal
                INNER JOIN people p ON p.id = lal.person_id
                WHERE 1 = 1 {$personWhere['sql']} {$dateSql}");
            $stmt->execute($params);
            $logs = $stmt->fetch() ?: [];
            $logSeconds = (int)($logs['log_seconds'] ?? 0);
            $logEvents = (int)($logs['events_total'] ?? 0);
        } catch (Throwable) {
            $logSeconds = 0;
            $logEvents = 0;
        }

        $attemptSeconds = (int)($attempts['attempt_seconds'] ?? 0);
        $totalSeconds = $attemptSeconds + $logSeconds;
        return [
            'finished_attempts' => (int)($attempts['finished_attempts'] ?? 0),
            'activity_events' => $logEvents,
            'total_seconds' => $totalSeconds,
            'total_hours' => round($totalSeconds / 3600, 2),
            'avg_attempt_minutes' => round((float)($attempts['avg_attempt_minutes'] ?? 0), 2),
        ];
    }

    private function pendingPeople(PDO $pdo, array $filters): array
    {
        $params = [];
        $assignedSql = $this->assignedModulesSubquery($filters, $params);
        $sql = "SELECT
                a.person_id,
                a.full_name,
                a.document_number,
                a.person_type,
                a.work_site,
                COALESCE(a.department, 'Contratistas') AS department,
                COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) AS assigned_total,
                COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) AS completed_total
            FROM ({$assignedSql}) a
            LEFT JOIN certificates cert ON cert.person_id = a.person_id
                AND cert.module_id = a.module_id
                AND cert.status = 'vigente'
                AND cert.expires_at >= NOW()
            GROUP BY a.person_id, a.full_name, a.document_number, a.person_type, a.work_site, COALESCE(a.department, 'Contratistas')
            HAVING COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END) < COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id))
            ORDER BY (COUNT(DISTINCT CONCAT(a.person_id, '-', a.module_id)) - COUNT(DISTINCT CASE WHEN cert.id IS NOT NULL THEN CONCAT(a.person_id, '-', a.module_id) END)) DESC, a.work_site, a.full_name
            LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $assigned = (int)$row['assigned_total'];
            $completed = (int)$row['completed_total'];
            $row['pending_total'] = max(0, $assigned - $completed);
            $row['completion_percent'] = $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0.0;
        }
        unset($row);
        return $rows;
    }

    private function assignedModulesSubquery(array $filters, array &$params): string
    {
        $where = $this->personWhere($filters, 'p');
        $params = array_merge($params, $where['params']);
        return "SELECT DISTINCT
                p.id AS person_id,
                p.full_name,
                p.document_number,
                p.person_type,
                p.work_site,
                p.department,
                p.position,
                c.id AS course_id,
                c.code AS course_code,
                c.title AS course_title,
                m.id AS module_id,
                m.title AS module_title,
                m.estimated_minutes
            FROM people p
            INNER JOIN course_assignments ca ON ca.active = 1
                AND (ca.person_type = 'todos' OR ca.person_type = p.person_type)
                AND (ca.work_site = 'todos' OR ca.work_site = p.work_site)
                AND (p.person_type = 'contratista' OR ca.department = 'todos' OR ca.department = p.department)
                AND (ca.position_keyword IS NULL OR ca.position_keyword = '' OR p.position LIKE CONCAT('%', ca.position_keyword, '%'))
            INNER JOIN courses c ON c.id = ca.course_id AND c.status = 'activo'
            INNER JOIN course_modules m ON m.course_id = c.id AND m.status = 'activo'
            WHERE p.status = 'activo' {$where['sql']}";
    }

    private function personWhere(array $filters, string $alias = 'p'): array
    {
        $sql = '';
        $params = [];
        if (($filters['work_site'] ?? '') !== '') {
            $sql .= " AND {$alias}.work_site = :{$alias}_work_site";
            $params[":{$alias}_work_site"] = $filters['work_site'];
        }
        if (($filters['person_type'] ?? '') !== '') {
            $sql .= " AND {$alias}.person_type = :{$alias}_person_type";
            $params[":{$alias}_person_type"] = $filters['person_type'];
        }
        if (($filters['department'] ?? '') !== '') {
            $sql .= " AND {$alias}.department = :{$alias}_department";
            $params[":{$alias}_department"] = $filters['department'];
        }
        return ['sql' => $sql, 'params' => $params];
    }

    private function attemptWhere(array $filters, string $personAlias = 'p', string $attemptAlias = 'ea'): array
    {
        $personWhere = $this->personWhere($filters, $personAlias);
        $sql = $personWhere['sql'];
        $params = $personWhere['params'];
        if (($filters['date_from'] ?? '') !== '') {
            $sql .= " AND {$attemptAlias}.started_at >= :{$attemptAlias}_date_from";
            $params[":{$attemptAlias}_date_from"] = $filters['date_from'] . ' 00:00:00';
        }
        if (($filters['date_to'] ?? '') !== '') {
            $sql .= " AND {$attemptAlias}.started_at <= :{$attemptAlias}_date_to";
            $params[":{$attemptAlias}_date_to"] = $filters['date_to'] . ' 23:59:59';
        }
        return ['sql' => $sql, 'params' => $params];
    }

    private function addCoverageFields(array $row): array
    {
        $people = (int)($row['people_total'] ?? 0);
        $certifiedPeople = (int)($row['people_certified'] ?? 0);
        $assigned = (int)($row['assigned_total'] ?? 0);
        $completed = (int)($row['completed_total'] ?? 0);
        $row['people_total'] = $people;
        $row['people_certified'] = $certifiedPeople;
        $row['assigned_total'] = $assigned;
        $row['completed_total'] = $completed;
        $row['pending_total'] = max(0, $assigned - $completed);
        $row['coverage_percent'] = $people > 0 ? round(($certifiedPeople / $people) * 100, 2) : 0.0;
        $row['completion_percent'] = $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0.0;
        return $row;
    }

    private function addApprovalFields(array $row): array
    {
        $attempts = (int)($row['attempts_total'] ?? 0);
        $passed = (int)($row['passed_total'] ?? 0);
        $row['attempts_total'] = $attempts;
        $row['passed_total'] = $passed;
        $row['failed_total'] = max(0, $attempts - $passed);
        $row['approval_rate_percent'] = $attempts > 0 ? round(($passed / $attempts) * 100, 2) : 0.0;
        $row['avg_score'] = round((float)($row['avg_score'] ?? 0), 2);
        return $row;
    }

    private function isDate(string $value): bool
    {
        if ($value === '') {
            return true;
        }
        $dt = DateTime::createFromFormat('Y-m-d', $value);
        return $dt !== false && $dt->format('Y-m-d') === $value;
    }
}
