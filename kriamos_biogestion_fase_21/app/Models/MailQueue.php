<?php

namespace App\Models;

class MailQueue extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO mail_queue (
                usuario_id, alerta_id, notificacion_id, destinatario, asunto, cuerpo_html,
                prioridad, estado, intentos, programado_at, created_at
            ) VALUES (
                :usuario_id, :alerta_id, :notificacion_id, :destinatario, :asunto, :cuerpo_html,
                :prioridad, :estado, :intentos, :programado_at, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function pendientes(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM mail_queue
            WHERE estado IN ('pendiente','error')
              AND intentos < 5
              AND (programado_at IS NULL OR programado_at <= NOW())
            ORDER BY FIELD(prioridad, 'critica','alta','media','baja'), created_at ASC
            LIMIT :limit
        ");
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT q.*, u.email AS usuario_email
            FROM mail_queue q
            LEFT JOIN usuarios u ON u.id = q.usuario_id
            ORDER BY q.created_at DESC
            LIMIT 300
        ");
        return $stmt->fetchAll();
    }

    public function marcarEnviado(int $id, string $respuesta): void
    {
        $stmt = $this->db->prepare("
            UPDATE mail_queue
            SET estado = 'enviado',
                enviado_at = NOW(),
                respuesta_smtp = :respuesta_smtp
            WHERE id = :id
        ");
        $stmt->execute([
            'respuesta_smtp' => $respuesta,
            'id' => $id,
        ]);
    }

    public function marcarError(int $id, string $error): void
    {
        $stmt = $this->db->prepare("
            UPDATE mail_queue
            SET estado = 'error',
                intentos = intentos + 1,
                respuesta_smtp = :respuesta_smtp
            WHERE id = :id
        ");
        $stmt->execute([
            'respuesta_smtp' => $error,
            'id' => $id,
        ]);
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM mail_queue WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);

        return (int) $stmt->fetchColumn();
    }
}
