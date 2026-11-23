<?php

namespace App\Models;

class AiPromptTemplate extends BaseModel
{
    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ai_prompt_templates
            WHERE codigo = :codigo
              AND activo = 1
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
            FROM ai_prompt_templates
            ORDER BY codigo ASC
        ");

        return $stmt->fetchAll();
    }
}
