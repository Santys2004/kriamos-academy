<?php

namespace App\Models;

class IcaExpediente extends BaseModel
{
    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['granja_id'])) {
            $where[] = 'e.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'e.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'e.fecha_inicio >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = 'e.fecha_fin <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT e.*, g.sigla AS granja_sigla, g.nombre AS granja_nombre,
                   u.email AS creado_por_email,
                   COUNT(DISTINCT h.id) AS total_hallazgos,
                   COUNT(DISTINCT p.id) AS total_planes
            FROM ica_expedientes e
            INNER JOIN granjas g ON g.id = e.granja_id
            LEFT JOIN usuarios u ON u.id = e.creado_por
            LEFT JOIN ica_hallazgos h ON h.expediente_id = e.id
            LEFT JOIN ica_planes_accion p ON p.expediente_id = e.id
            $sqlWhere
            GROUP BY e.id
            ORDER BY e.created_at DESC
            LIMIT 500
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, g.sigla AS granja_sigla, g.nombre AS granja_nombre,
                   u.email AS creado_por_email
            FROM ica_expedientes e
            INNER JOIN granjas g ON g.id = e.granja_id
            LEFT JOIN usuarios u ON u.id = e.creado_por
            WHERE e.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ica_expedientes (
                codigo, nombre, granja_id, fecha_inicio, fecha_fin, objetivo,
                alcance, estado, creado_por, created_at
            ) VALUES (
                :codigo, :nombre, :granja_id, :fecha_inicio, :fecha_fin, :objetivo,
                :alcance, :estado, :creado_por, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare("
            UPDATE ica_expedientes
            SET estado = :estado,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'estado' => $estado,
            'id' => $id,
        ]);
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ica_expedientes WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);

        return (int) $stmt->fetchColumn();
    }
}
