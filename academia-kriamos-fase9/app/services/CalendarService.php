<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/MailService.php';

class CalendarService
{
    public function syncAllCertificates(PDO $pdo): int
    {
        $stmt = $pdo->query("SELECT id FROM certificates WHERE status IN ('vigente','vencido') ORDER BY id ASC");
        $ids = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        $count = 0;
        foreach ($ids as $id) {
            $this->syncCertificate($pdo, $id);
            $count++;
        }
        $this->refreshCalendarStatuses($pdo);
        return $count;
    }

    public function syncCertificate(PDO $pdo, int $certificateId): ?array
    {
        $certificate = $this->certificateCalendarData($pdo, $certificateId);
        if (!$certificate) {
            return null;
        }

        $stmt = $pdo->prepare("INSERT INTO training_calendar
            (person_id, course_id, module_id, certificate_id, last_passed_at, next_due_at, reminder_5d_at, reminder_1d_at, status, active, created_at, updated_at)
            VALUES (:person_id, :course_id, :module_id, :certificate_id, :last_passed_at, :next_due_at,
                    DATE_SUB(:next_due_at_5, INTERVAL 5 DAY), DATE_SUB(:next_due_at_1, INTERVAL 1 DAY),
                    :status, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                person_id = VALUES(person_id),
                course_id = VALUES(course_id),
                module_id = VALUES(module_id),
                last_passed_at = VALUES(last_passed_at),
                next_due_at = VALUES(next_due_at),
                reminder_5d_at = VALUES(reminder_5d_at),
                reminder_1d_at = VALUES(reminder_1d_at),
                status = VALUES(status),
                active = 1,
                updated_at = NOW()");
        $stmt->execute([
            ':person_id' => $certificate['person_id'],
            ':course_id' => $certificate['course_id'],
            ':module_id' => $certificate['module_id'],
            ':certificate_id' => $certificate['certificate_id'],
            ':last_passed_at' => $certificate['issued_at'],
            ':next_due_at' => $certificate['expires_at'],
            ':next_due_at_5' => $certificate['expires_at'],
            ':next_due_at_1' => $certificate['expires_at'],
            ':status' => $this->statusForDate((string)$certificate['expires_at']),
        ]);

        $calendar = $this->findByCertificateId($pdo, $certificateId);
        if ($calendar) {
            $this->ensureNotificationQueue($pdo, $calendar);
        }
        return $calendar;
    }

    public function refreshCalendarStatuses(PDO $pdo): void
    {
        $pdo->exec("UPDATE certificates SET status = 'vencido', updated_at = NOW()
            WHERE status = 'vigente' AND expires_at < NOW()");

        $pdo->exec("UPDATE training_calendar
            SET status = CASE
                WHEN next_due_at < NOW() THEN 'reactivado'
                WHEN next_due_at <= DATE_ADD(NOW(), INTERVAL 5 DAY) THEN 'por_vencer'
                ELSE 'vigente'
            END,
            updated_at = NOW()
            WHERE active = 1");
    }

    public function ensureNotificationQueue(PDO $pdo, array $calendar): void
    {
        $types = [
            'recordatorio_5_dias' => $calendar['reminder_5d_at'],
            'recordatorio_1_dia' => $calendar['reminder_1d_at'],
            'reactivacion' => $calendar['next_due_at'],
        ];

        foreach ($types as $type => $scheduledAt) {
            $message = $this->buildNotificationMessage($calendar, $type);
            $stmt = $pdo->prepare("INSERT IGNORE INTO notification_queue
                (training_calendar_id, person_id, course_id, module_id, certificate_id, notification_type, channel,
                 recipient_email, subject, body, scheduled_at, status, created_at, updated_at)
                VALUES (:training_calendar_id, :person_id, :course_id, :module_id, :certificate_id, :notification_type, 'email',
                        :recipient_email, :subject, :body, :scheduled_at, 'pendiente', NOW(), NOW())");
            $stmt->execute([
                ':training_calendar_id' => $calendar['id'],
                ':person_id' => $calendar['person_id'],
                ':course_id' => $calendar['course_id'],
                ':module_id' => $calendar['module_id'],
                ':certificate_id' => $calendar['certificate_id'],
                ':notification_type' => $type,
                ':recipient_email' => $calendar['email'],
                ':subject' => $message['subject'],
                ':body' => $message['body'],
                ':scheduled_at' => $scheduledAt,
            ]);
        }
    }

    public function processDueNotifications(PDO $pdo, int $limit = 50): array
    {
        $this->refreshCalendarStatuses($pdo);
        $this->syncAllCertificates($pdo);

        $limit = max(1, min(200, $limit));
        $stmt = $pdo->query("SELECT * FROM notification_queue
            WHERE status = 'pendiente' AND scheduled_at <= NOW()
            ORDER BY scheduled_at ASC, id ASC
            LIMIT {$limit}");
        $notifications = $stmt->fetchAll();
        $mailer = new MailService();

        $summary = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach ($notifications as $notification) {
            $summary['processed']++;
            if (!filter_var((string)$notification['recipient_email'], FILTER_VALIDATE_EMAIL)) {
                $this->markNotification($pdo, (int)$notification['id'], 'omitido', 'Correo inválido o no registrado.');
                $summary['skipped']++;
                continue;
            }

            $result = $mailer->send($notification['recipient_email'], $notification['subject'], $notification['body']);
            if ($result['success']) {
                $this->markNotification($pdo, (int)$notification['id'], 'enviado', $result['message']);
                $summary['sent']++;
            } else {
                $this->markNotification($pdo, (int)$notification['id'], 'fallido', $result['message']);
                $summary['failed']++;
            }
        }
        return $summary;
    }

    private function markNotification(PDO $pdo, int $id, string $status, ?string $message = null): void
    {
        $stmt = $pdo->prepare("UPDATE notification_queue
            SET status = :status,
                sent_at = CASE WHEN :sent_status = 'enviado' THEN NOW() ELSE sent_at END,
                error_message = :message,
                updated_at = NOW()
            WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':sent_status' => $status,
            ':message' => $message,
            ':id' => $id,
        ]);
    }

    public function listByPersonDocument(PDO $pdo, string $document): array
    {
        $this->refreshCalendarStatuses($pdo);
        $stmt = $pdo->prepare($this->calendarSelectSql() . " WHERE p.document_number = :document ORDER BY tc.next_due_at ASC, tc.id DESC");
        $stmt->execute([':document' => $document]);
        return $stmt->fetchAll();
    }

    public function listAdmin(PDO $pdo, array $filters = [], int $limit = 300): array
    {
        $this->refreshCalendarStatuses($pdo);
        $where = ['tc.active = 1'];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], ['vigente','por_vencer','vencido','reactivado'], true)) {
            $where[] = 'tc.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['work_site']) && in_array($filters['work_site'], ['GAEE','GALM','Oficina'], true)) {
            $where[] = 'p.work_site = :work_site';
            $params[':work_site'] = $filters['work_site'];
        }
        if (!empty($filters['person_type']) && in_array($filters['person_type'], ['empleado','contratista'], true)) {
            $where[] = 'p.person_type = :person_type';
            $params[':person_type'] = $filters['person_type'];
        }

        $limit = max(1, min(1000, $limit));
        $sql = $this->calendarSelectSql() . ' WHERE ' . implode(' AND ', $where) . " ORDER BY tc.next_due_at ASC, tc.id DESC LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function stats(PDO $pdo): array
    {
        $this->refreshCalendarStatuses($pdo);
        $base = ['total' => 0, 'vigente' => 0, 'por_vencer' => 0, 'vencido' => 0, 'reactivado' => 0, 'pendientes_email' => 0];
        $stmt = $pdo->query("SELECT status, COUNT(*) AS total FROM training_calendar WHERE active = 1 GROUP BY status");
        foreach ($stmt->fetchAll() as $row) {
            $base[$row['status']] = (int)$row['total'];
            $base['total'] += (int)$row['total'];
        }
        $stmtPending = $pdo->query("SELECT COUNT(*) FROM notification_queue WHERE status = 'pendiente'");
        $base['pendientes_email'] = (int)$stmtPending->fetchColumn();
        return $base;
    }

    public function notificationHistory(PDO $pdo, int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $pdo->query("SELECT nq.*, p.full_name, p.document_number, c.code AS course_code, c.title AS course_title, m.title AS module_title
            FROM notification_queue nq
            INNER JOIN people p ON p.id = nq.person_id
            INNER JOIN courses c ON c.id = nq.course_id
            INNER JOIN course_modules m ON m.id = nq.module_id
            ORDER BY nq.scheduled_at DESC, nq.id DESC
            LIMIT {$limit}");
        return $stmt->fetchAll();
    }

    public function sendManualReminder(PDO $pdo, int $calendarId): void
    {
        $calendar = $this->findById($pdo, $calendarId);
        if (!$calendar) {
            throw new RuntimeException('No se encontró la programación seleccionada.');
        }
        $message = $this->buildNotificationMessage($calendar, 'manual');
        $stmt = $pdo->prepare("INSERT INTO notification_queue
            (training_calendar_id, person_id, course_id, module_id, certificate_id, notification_type, channel,
             recipient_email, subject, body, scheduled_at, status, created_at, updated_at)
            VALUES (:training_calendar_id, :person_id, :course_id, :module_id, :certificate_id, 'manual', 'email',
                    :recipient_email, :subject, :body, NOW(), 'pendiente', NOW(), NOW())");
        $stmt->execute([
            ':training_calendar_id' => $calendar['id'],
            ':person_id' => $calendar['person_id'],
            ':course_id' => $calendar['course_id'],
            ':module_id' => $calendar['module_id'],
            ':certificate_id' => $calendar['certificate_id'],
            ':recipient_email' => $calendar['email'],
            ':subject' => $message['subject'],
            ':body' => $message['body'],
        ]);
    }

    private function certificateCalendarData(PDO $pdo, int $certificateId): ?array
    {
        $stmt = $pdo->prepare("SELECT
                cert.id AS certificate_id,
                cert.person_id,
                cert.course_id,
                cert.module_id,
                cert.issued_at,
                cert.expires_at,
                cert.status AS certificate_status,
                p.full_name,
                p.document_number,
                p.person_type,
                p.email,
                p.work_site,
                p.company,
                p.position,
                p.department,
                c.code AS course_code,
                c.title AS course_title,
                m.title AS module_title
            FROM certificates cert
            INNER JOIN people p ON p.id = cert.person_id
            INNER JOIN courses c ON c.id = cert.course_id
            INNER JOIN course_modules m ON m.id = cert.module_id
            WHERE cert.id = :id
            LIMIT 1");
        $stmt->execute([':id' => $certificateId]);
        return $stmt->fetch() ?: null;
    }

    private function findByCertificateId(PDO $pdo, int $certificateId): ?array
    {
        $stmt = $pdo->prepare($this->calendarSelectSql() . " WHERE tc.certificate_id = :certificate_id LIMIT 1");
        $stmt->execute([':certificate_id' => $certificateId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(PDO $pdo, int $calendarId): ?array
    {
        $stmt = $pdo->prepare($this->calendarSelectSql() . " WHERE tc.id = :id LIMIT 1");
        $stmt->execute([':id' => $calendarId]);
        return $stmt->fetch() ?: null;
    }

    private function calendarSelectSql(): string
    {
        return "SELECT
                tc.*,
                p.full_name,
                p.document_number,
                p.person_type,
                p.email,
                p.cellphone,
                p.work_site,
                p.company,
                p.position,
                p.department,
                c.code AS course_code,
                c.title AS course_title,
                m.title AS module_title,
                cert.certificate_code,
                cert.verification_token,
                cert.status AS certificate_status
            FROM training_calendar tc
            INNER JOIN people p ON p.id = tc.person_id
            INNER JOIN courses c ON c.id = tc.course_id
            INNER JOIN course_modules m ON m.id = tc.module_id
            INNER JOIN certificates cert ON cert.id = tc.certificate_id";
    }

    private function statusForDate(string $nextDueAt): string
    {
        $due = strtotime($nextDueAt);
        if (!$due) {
            return 'vigente';
        }
        $now = time();
        if ($due < $now) {
            return 'reactivado';
        }
        if ($due <= strtotime('+5 days', $now)) {
            return 'por_vencer';
        }
        return 'vigente';
    }

    private function buildNotificationMessage(array $calendar, string $type): array
    {
        $name = e($calendar['full_name'] ?? 'Participante');
        $course = e(($calendar['course_code'] ?? '') . ' - ' . ($calendar['course_title'] ?? 'Curso'));
        $module = e($calendar['module_title'] ?? 'Módulo');
        $dueDate = human_datetime($calendar['next_due_at'] ?? '');
        $baseUrl = rtrim((string)app_config('base_url'), '/');
        $url = $baseUrl !== '' ? $baseUrl . '/index.php?page=mis_capacitaciones' : 'Ingrese a Academia KRIAMOS.';

        $subject = match ($type) {
            'recordatorio_5_dias' => 'Academia KRIAMOS: capacitación próxima a reactivarse en 5 días',
            'recordatorio_1_dia' => 'Academia KRIAMOS: capacitación se reactiva mañana',
            'reactivacion' => 'Academia KRIAMOS: capacitación reactivada para nuevo ciclo',
            default => 'Academia KRIAMOS: recordatorio de capacitación',
        };

        $intro = match ($type) {
            'recordatorio_5_dias' => 'Tu capacitación se reactivará en aproximadamente cinco días. Te recomendamos programar el repaso del contenido.',
            'recordatorio_1_dia' => 'Tu capacitación se reactivará mañana. Recuerda que debes mantener tus módulos vigentes para el ingreso y la operación segura.',
            'reactivacion' => 'Tu capacitación ya se encuentra reactivada para el nuevo ciclo de seis meses. Debes realizar nuevamente el módulo correspondiente.',
            default => 'Tienes un recordatorio asociado a tu calendario de capacitación.',
        };

        $body = "<div style='font-family:Arial,sans-serif;color:#12365A'>
            <h2 style='color:#12365A'>Academia KRIAMOS</h2>
            <p>Hola <strong>{$name}</strong>,</p>
            <p>{$intro}</p>
            <table style='border-collapse:collapse;width:100%;max-width:680px'>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Curso</strong></td><td style='padding:8px;border:1px solid #ddd'>{$course}</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Módulo</strong></td><td style='padding:8px;border:1px solid #ddd'>{$module}</td></tr>
                <tr><td style='padding:8px;border:1px solid #ddd'><strong>Fecha de nuevo ciclo</strong></td><td style='padding:8px;border:1px solid #ddd'>{$dueDate}</td></tr>
            </table>
            <p style='margin-top:18px'>Ingresa a la plataforma desde: <strong>{$url}</strong></p>
            <p style='color:#555;font-size:13px'>Mensaje automático generado por Academia KRIAMOS. Si tienes dudas, comunícate con tu administrador o jefe inmediato.</p>
        </div>";

        return ['subject' => $subject, 'body' => $body];
    }
}
