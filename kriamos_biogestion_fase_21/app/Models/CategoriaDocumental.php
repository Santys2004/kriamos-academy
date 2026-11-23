<?php

namespace App\Models;

class CategoriaDocumental extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM categorias_documentales
            WHERE activo = 1
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categorias_documentales WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
