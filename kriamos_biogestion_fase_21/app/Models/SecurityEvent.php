<?php

namespace App\Models;

class SecurityEvent extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_events (
                usuario_id, tipo, nivel, descripcion, ip, dispositivo, contexto_json, created_at
            ) VALUES (
                :usuario_id, :tipo, :nivel, :descripcion, :ip, :dispositivo, :contexto_json, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT e.*, u.email
            FROM security_events e
            LEFT JOIN usuarios u ON u.id = e.usuario_id
            ORDER BY e.created_at DESC
            LIMIT 200
        ");
        return $stmt->fetchAll();
    }

    public function countByNivel(string $nivel): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM security_events WHERE nivel = :nivel");
        $stmt->execute(['nivel' => $nivel]);
        return (int) $stmt->fetchColumn();
    }
}
