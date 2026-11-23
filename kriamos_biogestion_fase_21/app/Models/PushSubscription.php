<?php

namespace App\Models;

class PushSubscription extends BaseModel
{
    public function saveSubscription(int $usuarioId, array $subscription): int
    {
        $endpoint = $subscription['endpoint'] ?? '';
        $keys = $subscription['keys'] ?? [];

        $stmt = $this->db->prepare("
            INSERT INTO push_subscriptions (
                usuario_id, endpoint, p256dh, auth, user_agent, activo, created_at, updated_at
            ) VALUES (
                :usuario_id, :endpoint, :p256dh, :auth, :user_agent, 1, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                p256dh = VALUES(p256dh),
                auth = VALUES(auth),
                user_agent = VALUES(user_agent),
                activo = 1,
                updated_at = NOW()
        ");

        $stmt->execute([
            'usuario_id' => $usuarioId,
            'endpoint' => $endpoint,
            'p256dh' => $keys['p256dh'] ?? null,
            'auth' => $keys['auth'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function activeByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM push_subscriptions
            WHERE usuario_id = :usuario_id
              AND activo = 1
        ");
        $stmt->execute(['usuario_id' => $usuarioId]);

        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM push_subscriptions WHERE activo = 1")->fetchColumn();
    }
}
