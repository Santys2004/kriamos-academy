<?php

namespace App\Models;

class TipoFormato extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM tipos_formato
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM tipos_formato
            WHERE id = :id AND activo = 1
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tipos_formato WHERE activo = 1")->fetchColumn();
    }
}
