<?php

namespace App\Models;

class IcaMatrizDocumento extends BaseModel
{
    public function byExpediente(int $expedienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, d.titulo AS documento_titulo, d.estado AS documento_estado,
                   tf.nombre AS tipo_formato_nombre,
                   c.numero_certificacion
            FROM ica_matriz_documental m
            LEFT JOIN documentos d ON d.id = m.documento_id
            LEFT JOIN tipos_formato tf ON tf.id = m.tipo_formato_id
            LEFT JOIN certificaciones_granja c ON c.id = m.certificacion_id
            WHERE m.expediente_id = :expediente_id
            ORDER BY m.categoria ASC, m.requisito ASC
        ");
        $stmt->execute(['expediente_id' => $expedienteId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ica_matriz_documental (
                expediente_id, categoria, requisito, descripcion, documento_id,
                tipo_formato_id, certificacion_id, obligatorio, estado_cumplimiento,
                observacion, created_at
            ) VALUES (
                :expediente_id, :categoria, :requisito, :descripcion, :documento_id,
                :tipo_formato_id, :certificacion_id, :obligatorio, :estado_cumplimiento,
                :observacion, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateCumplimiento(int $id, string $estado, ?string $observacion): void
    {
        $stmt = $this->db->prepare("
            UPDATE ica_matriz_documental
            SET estado_cumplimiento = :estado,
                observacion = :observacion,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'estado' => $estado,
            'observacion' => $observacion,
            'id' => $id,
        ]);
    }

    public function resumenCumplimiento(int $expedienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado_cumplimiento = 'cumple' THEN 1 ELSE 0 END) AS cumple,
                SUM(CASE WHEN estado_cumplimiento = 'no_cumple' THEN 1 ELSE 0 END) AS no_cumple,
                SUM(CASE WHEN estado_cumplimiento = 'no_aplica' THEN 1 ELSE 0 END) AS no_aplica,
                SUM(CASE WHEN estado_cumplimiento = 'pendiente' THEN 1 ELSE 0 END) AS pendiente
            FROM ica_matriz_documental
            WHERE expediente_id = :expediente_id
        ");
        $stmt->execute(['expediente_id' => $expedienteId]);

        $row = $stmt->fetch() ?: [];
        $total = (int) ($row['total'] ?? 0);
        $cumple = (int) ($row['cumple'] ?? 0);

        $row['porcentaje_cumplimiento'] = $total > 0 ? round(($cumple / $total) * 100, 2) : 0;

        return $row;
    }
}
