<?php

namespace App\Models;

class ApiToken extends BaseModel
{
    public function create(int $clientId, string $plainToken, ?string $expiresAt, int $createdBy): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (
                api_client_id, token_hash, token_prefix, estado, expires_at, creado_por, created_at
            ) VALUES (
                :api_client_id, :token_hash, :token_prefix, 'activo', :expires_at, :creado_por, NOW()
            )
        ");
        $stmt->execute([
            'api_client_id' => $clientId,
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => substr($plainToken, 0, 12),
            'expires_at' => $expiresAt,
            'creado_por' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findValidByPlainToken(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);

        $stmt = $this->db->prepare("
            SELECT t.*, c.nombre AS cliente_nombre, c.estado AS cliente_estado, c.ip_permitidas
            FROM api_tokens t
            INNER JOIN api_clients c ON c.id = t.api_client_id
            WHERE t.token_hash = :hash
              AND t.estado = 'activo'
              AND c.estado = 'activo'
              AND (t.expires_at IS NULL OR t.expires_at >= NOW())
            LIMIT 1
        ");
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function revoke(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE api_tokens
            SET estado = 'revocado',
                revoked_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public function touchLastUsed(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE api_tokens
            SET last_used_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}
