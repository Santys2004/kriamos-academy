<?php

namespace App\Models;

class BiExternalLog extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO bi_external_logs (
                connection_id, dataset_codigo, formato, status_code, ip,
                user_agent, response_rows, response_ms, created_at
            ) VALUES (
                :connection_id, :dataset_codigo, :formato, :status_code, :ip,
                :user_agent, :response_rows, :response_ms, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT l.*, c.nombre AS conexion_nombre, c.herramienta
            FROM bi_external_logs l
            LEFT JOIN bi_external_connections c ON c.id = l.connection_id
            ORDER BY l.created_at DESC
            LIMIT 300
        ");
        return $stmt->fetchAll();
    }

    public function countToday(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM bi_external_logs
            WHERE DATE(created_at) = CURDATE()
        ")->fetchColumn();
    }
}
