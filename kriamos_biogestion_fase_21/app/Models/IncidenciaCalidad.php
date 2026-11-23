<?php

namespace App\Models;

class IncidenciaCalidad extends BaseModel
{
    public function create(int $registroId, string $tipo, string $campo, string $mensaje, string $nivel = 'media'): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO incidencias_calidad (
                formato_registro_id, tipo, campo, mensaje, nivel, estado, created_at
            ) VALUES (
                :formato_registro_id, :tipo, :campo, :mensaje, :nivel, 'abierta', NOW()
            )
        ");

        $stmt->execute([
            'formato_registro_id' => $registroId,
            'tipo' => $tipo,
            'campo' => $campo,
            'mensaje' => $mensaje,
            'nivel' => $nivel,
        ]);
    }

    public function byRegistro(int $registroId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM incidencias_calidad
            WHERE formato_registro_id = :registro_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['registro_id' => $registroId]);

        return $stmt->fetchAll();
    }

    public function abiertas(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM incidencias_calidad WHERE estado = 'abierta'")->fetchColumn();
    }
}
