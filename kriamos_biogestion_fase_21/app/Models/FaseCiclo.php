<?php

namespace App\Models;

class FaseCiclo extends BaseModel
{
    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM fases_ciclo WHERE activo = 1 ORDER BY orden ASC");
        return $stmt->fetchAll();
    }
}
