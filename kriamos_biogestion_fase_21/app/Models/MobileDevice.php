<?php

namespace App\Models;

class MobileDevice extends BaseModel
{
    public function register(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO mobile_devices (
                usuario_id, platform, device_uuid, device_name, app_version,
                push_token, estado, last_seen_at, created_at, updated_at
            ) VALUES (
                :usuario_id, :platform, :device_uuid, :device_name, :app_version,
                :push_token, 'activo', NOW(), NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                platform = VALUES(platform),
                device_name = VALUES(device_name),
                app_version = VALUES(app_version),
                push_token = VALUES(push_token),
                estado = 'activo',
                last_seen_at = NOW(),
                updated_at = NOW()
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT d.*, u.email, u.nombres, u.apellidos
            FROM mobile_devices d
            LEFT JOIN usuarios u ON u.id = d.usuario_id
            ORDER BY d.last_seen_at DESC
            LIMIT 300
        ");
        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM mobile_devices WHERE estado = 'activo'")->fetchColumn();
    }
}
