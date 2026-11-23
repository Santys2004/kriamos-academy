<?php

namespace App\Models;

class IcaPlanAccion extends BaseModel
{
    public function byExpediente(int $expedienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, h.titulo AS hallazgo_titulo,
                   u.email AS responsable_email
            FROM ica_planes_accion p
            LEFT JOIN ica_hallazgos h ON h.id = p.hallazgo_id
            LEFT JOIN usuarios u ON u.id = p.responsable_id
            WHERE p.expediente_id = :expediente_id
            ORDER BY p.fecha_compromiso ASC
        ");
        $stmt->execute(['expediente_id' => $expedienteId]);

        return $stmt->fetchAll();
    }

    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['estado'])) {
            $where[] = 'p.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['responsable_id'])) {
            $where[] = 'p.responsable_id = :responsable_id';
            $params['responsable_id'] = (int) $filters['responsable_id'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT p.*, e.codigo AS expediente_codigo, g.sigla AS granja_sigla,
                   h.titulo AS hallazgo_titulo, u.email AS responsable_email
            FROM ica_planes_accion p
            INNER JOIN ica_expedientes e ON e.id = p.expediente_id
            INNER JOIN granjas g ON g.id = e.granja_id
            LEFT JOIN ica_hallazgos h ON h.id = p.hallazgo_id
            LEFT JOIN usuarios u ON u.id = p.responsable_id
            $sqlWhere
            ORDER BY p.fecha_compromiso ASC
            LIMIT 500
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ica_planes_accion (
                expediente_id, hallazgo_id, accion, responsable_id,
                fecha_compromiso, estado, avance_porcentaje, observaciones, created_at
            ) VALUES (
                :expediente_id, :hallazgo_id, :accion, :responsable_id,
                :fecha_compromiso, :estado, :avance_porcentaje, :observaciones, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateAvance(int $id, string $estado, int $avance, ?string $observaciones): void
    {
        $stmt = $this->db->prepare("
            UPDATE ica_planes_accion
            SET estado = :estado,
                avance_porcentaje = :avance,
                observaciones = :observaciones,
                cerrado_at = CASE WHEN :estado = 'cerrado' THEN NOW() ELSE cerrado_at END,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'estado' => $estado,
            'avance' => $avance,
            'observaciones' => $observaciones,
            'id' => $id,
        ]);
    }

    public function countVencidos(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM ica_planes_accion
            WHERE fecha_compromiso < CURDATE()
              AND estado NOT IN ('cerrado','cancelado')
        ")->fetchColumn();
    }
}
