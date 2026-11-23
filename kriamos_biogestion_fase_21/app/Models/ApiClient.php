<?php

namespace App\Models;

class ApiClient extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, u.email AS creado_por_email,
                   COUNT(t.id) AS total_tokens
            FROM api_clients c
            LEFT JOIN usuarios u ON u.id = c.creado_por
            LEFT JOIN api_tokens t ON t.api_client_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM api_clients
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO api_clients (
                nombre, descripcion, contacto_email, estado, ip_permitidas, creado_por, created_at
            ) VALUES (
                :nombre, :descripcion, :contacto_email, :estado, :ip_permitidas, :creado_por, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE api_clients
            SET nombre = :nombre,
                descripcion = :descripcion,
                contacto_email = :contacto_email,
                estado = :estado,
                ip_permitidas = :ip_permitidas
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function tokens(int $clientId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM api_tokens
            WHERE api_client_id = :client_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }
}
