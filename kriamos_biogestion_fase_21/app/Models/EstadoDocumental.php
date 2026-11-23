<?php

namespace App\Models;

class EstadoDocumental extends BaseModel
{
    public function findByCode(string $codigo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM estados_documentales
            WHERE codigo = :codigo
            LIMIT 1
        ");
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM estados_documentales
            WHERE activo = 1
            ORDER BY id ASC
        ");
        return $stmt->fetchAll();
    }
}
