<?php

namespace App\Models;

class ManualPaso extends BaseModel
{
    public function byModulo(string $modulo): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM manual_pasos
            WHERE modulo = :modulo AND activo = 1
            ORDER BY orden ASC
        ");
        $stmt->execute(['modulo' => $modulo]);
        return $stmt->fetchAll();
    }

    public function modules(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT modulo
            FROM manual_pasos
            WHERE activo = 1
            ORDER BY modulo ASC
        ");
        return array_column($stmt->fetchAll(), 'modulo');
    }
}
