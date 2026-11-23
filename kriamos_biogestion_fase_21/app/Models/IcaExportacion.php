<?php

namespace App\Models;

class IcaExportacion extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ica_exportaciones (
                expediente_id, tipo, archivo_path, hash_sha256, total_archivos,
                generado_por, estado, created_at
            ) VALUES (
                :expediente_id, :tipo, :archivo_path, :hash_sha256, :total_archivos,
                :generado_por, :estado, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function byExpediente(int $expedienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT ex.*, u.email AS generado_por_email
            FROM ica_exportaciones ex
            LEFT JOIN usuarios u ON u.id = ex.generado_por
            WHERE ex.expediente_id = :expediente_id
            ORDER BY ex.created_at DESC
        ");
        $stmt->execute(['expediente_id' => $expedienteId]);

        return $stmt->fetchAll();
    }
}
