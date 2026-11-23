<?php

namespace App\Models;

class IcaHallazgo extends BaseModel
{
    public function byExpediente(int $expedienteId): array
    {
        $stmt = $this->db->prepare("
            SELECT h.*, u.email AS reportado_por_email
            FROM ica_hallazgos h
            LEFT JOIN usuarios u ON u.id = h.reportado_por
            WHERE h.expediente_id = :expediente_id
            ORDER BY FIELD(h.criticidad, 'critica','alta','media','baja'), h.created_at DESC
        ");
        $stmt->execute(['expediente_id' => $expedienteId]);

        return $stmt->fetchAll();
    }

    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['criticidad'])) {
            $where[] = 'h.criticidad = :criticidad';
            $params['criticidad'] = $filters['criticidad'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'h.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['expediente_id'])) {
            $where[] = 'h.expediente_id = :expediente_id';
            $params['expediente_id'] = (int) $filters['expediente_id'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT h.*, e.codigo AS expediente_codigo, g.sigla AS granja_sigla,
                   u.email AS reportado_por_email
            FROM ica_hallazgos h
            INNER JOIN ica_expedientes e ON e.id = h.expediente_id
            INNER JOIN granjas g ON g.id = e.granja_id
            LEFT JOIN usuarios u ON u.id = h.reportado_por
            $sqlWhere
            ORDER BY h.created_at DESC
            LIMIT 500
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ica_hallazgos (
                expediente_id, matriz_documental_id, titulo, descripcion,
                criticidad, estado, evidencia_path, reportado_por, created_at
            ) VALUES (
                :expediente_id, :matriz_documental_id, :titulo, :descripcion,
                :criticidad, :estado, :evidencia_path, :reportado_por, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function countByCriticidad(string $criticidad): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ica_hallazgos WHERE criticidad = :criticidad");
        $stmt->execute(['criticidad' => $criticidad]);

        return (int) $stmt->fetchColumn();
    }
}
