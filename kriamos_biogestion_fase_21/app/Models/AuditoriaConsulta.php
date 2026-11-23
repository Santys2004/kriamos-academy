<?php

namespace App\Models;

class AuditoriaConsulta extends BaseModel
{
    public function search(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'a.usuario_id = :usuario_id';
            $params['usuario_id'] = (int) $filters['usuario_id'];
        }

        if (!empty($filters['modulo'])) {
            $where[] = 'a.modulo = :modulo';
            $params['modulo'] = $filters['modulo'];
        }

        if (!empty($filters['accion'])) {
            $where[] = 'a.accion = :accion';
            $params['accion'] = $filters['accion'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'DATE(a.created_at) >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = 'DATE(a.created_at) <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT a.*, u.email, u.nombres, u.apellidos
            FROM auditoria a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            $sqlWhere
            ORDER BY a.created_at DESC
            LIMIT 1000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function modules(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT modulo
            FROM auditoria
            WHERE modulo IS NOT NULL
            ORDER BY modulo ASC
        ");
        return array_column($stmt->fetchAll(), 'modulo');
    }
}
