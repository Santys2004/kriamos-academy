<?php

namespace App\Models;

class ApiLog extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO api_logs (
                api_client_id, api_token_id, metodo, endpoint, status_code,
                ip, user_agent, request_hash, response_ms, created_at
            ) VALUES (
                :api_client_id, :api_token_id, :metodo, :endpoint, :status_code,
                :ip, :user_agent, :request_hash, :response_ms, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function recientes(): array
    {
        $stmt = $this->db->query("
            SELECT l.*, c.nombre AS cliente_nombre
            FROM api_logs l
            LEFT JOIN api_clients c ON c.id = l.api_client_id
            ORDER BY l.created_at DESC
            LIMIT 300
        ");
        return $stmt->fetchAll();
    }

    public function countToday(?int $tokenId = null): int
    {
        if ($tokenId) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM api_logs
                WHERE api_token_id = :token_id
                  AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute(['token_id' => $tokenId]);
            return (int) $stmt->fetchColumn();
        }

        return (int) $this->db->query("SELECT COUNT(*) FROM api_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    }

    public function countByStatusPrefix(string $prefix): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM api_logs
            WHERE CAST(status_code AS CHAR) LIKE :prefix
        ");
        $stmt->execute(['prefix' => $prefix . '%']);
        return (int) $stmt->fetchColumn();
    }
}
