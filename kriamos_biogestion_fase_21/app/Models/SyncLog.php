<?php

namespace App\Models;

class SyncLog extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO offline_sync_logs (
                usuario_id, local_id, tipo, estado, remote_id, payload_json,
                mensaje, ip, dispositivo, created_at
            ) VALUES (
                :usuario_id, :local_id, :tipo, :estado, :remote_id, :payload_json,
                :mensaje, :ip, :dispositivo, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT l.*, u.email
            FROM offline_sync_logs l
            LEFT JOIN usuarios u ON u.id = l.usuario_id
            ORDER BY l.created_at DESC
            LIMIT 200
        ");
        return $stmt->fetchAll();
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM offline_sync_logs WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }
}
