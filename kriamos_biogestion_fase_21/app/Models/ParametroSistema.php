<?php

namespace App\Models;

class ParametroSistema extends BaseModel
{
    public function all(?string $grupo = null): array
    {
        if ($grupo) {
            $stmt = $this->db->prepare("
                SELECT *
                FROM parametros_sistema
                WHERE grupo = :grupo
                ORDER BY grupo ASC, clave ASC
            ");
            $stmt->execute(['grupo' => $grupo]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->query("
            SELECT *
            FROM parametros_sistema
            ORDER BY grupo ASC, clave ASC
        ");
        return $stmt->fetchAll();
    }

    public function groups(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT grupo
            FROM parametros_sistema
            ORDER BY grupo ASC
        ");
        return array_column($stmt->fetchAll(), 'grupo');
    }

    public function updateValue(int $id, string $valor): void
    {
        $stmt = $this->db->prepare("
            UPDATE parametros_sistema
            SET valor = :valor,
                updated_at = NOW()
            WHERE id = :id AND editable = 1
        ");
        $stmt->execute([
            'valor' => $valor,
            'id' => $id,
        ]);
    }
}
