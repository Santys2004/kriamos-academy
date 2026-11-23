<?php

namespace App\Models;

class BiExternalDataset extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM bi_external_datasets
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function active(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM bi_external_datasets
            WHERE estado = 'activo'
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM bi_external_datasets
            WHERE codigo = :codigo
              AND estado = 'activo'
            LIMIT 1
        ");
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateExportInfo(int $id, string $path, int $rows): void
    {
        $stmt = $this->db->prepare("
            UPDATE bi_external_datasets
            SET ultimo_archivo_path = :path,
                ultima_cantidad_registros = :rows,
                ultimo_generado_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'path' => $path,
            'rows' => $rows,
            'id' => $id,
        ]);
    }
}
