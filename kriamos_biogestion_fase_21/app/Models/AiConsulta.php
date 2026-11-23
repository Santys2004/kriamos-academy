<?php

namespace App\Models;

class AiConsulta extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_consultas (
                usuario_id, pregunta, respuesta, modo, proveedor, contexto_ids_json,
                tokens_estimados, ip, dispositivo, created_at
            ) VALUES (
                :usuario_id, :pregunta, :respuesta, :modo, :proveedor, :contexto_ids_json,
                :tokens_estimados, :ip, :dispositivo, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function recientes(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'c.usuario_id = :usuario_id';
            $params['usuario_id'] = (int) $filters['usuario_id'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'DATE(c.created_at) >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = 'DATE(c.created_at) <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT c.*, u.email, u.nombres, u.apellidos
            FROM ai_consultas c
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            $sqlWhere
            ORDER BY c.created_at DESC
            LIMIT 300
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countToday(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ai_consultas WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    }

    public function countByModo(string $modo): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_consultas WHERE modo = :modo");
        $stmt->execute(['modo' => $modo]);

        return (int) $stmt->fetchColumn();
    }
}
