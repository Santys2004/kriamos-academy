<?php

namespace App\Models;

class BiExternalConnection extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, u.email AS creado_por_email,
                   COUNT(l.id) AS total_consumos
            FROM bi_external_connections c
            LEFT JOIN usuarios u ON u.id = c.creado_por
            LEFT JOIN bi_external_logs l ON l.connection_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM bi_external_connections
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
            INSERT INTO bi_external_connections (
                nombre, herramienta, descripcion, contacto_email, estado,
                ip_permitidas, token_hash, token_prefix, creado_por, created_at
            ) VALUES (
                :nombre, :herramienta, :descripcion, :contacto_email, :estado,
                :ip_permitidas, :token_hash, :token_prefix, :creado_por, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE bi_external_connections
            SET nombre = :nombre,
                herramienta = :herramienta,
                descripcion = :descripcion,
                contacto_email = :contacto_email,
                estado = :estado,
                ip_permitidas = :ip_permitidas
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function findByToken(string $plainToken): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM bi_external_connections
            WHERE token_hash = :token_hash
              AND estado = 'activo'
            LIMIT 1
        ");
        $stmt->execute(['token_hash' => hash('sha256', $plainToken)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function touch(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE bi_external_connections
            SET last_used_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}
