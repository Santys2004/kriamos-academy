<?php

namespace App\Models;

class Notificacion extends BaseModel
{
    public function all(?int $usuarioId = null): array
    {
        $where = '';
        $params = [];

        if ($usuarioId) {
            $where = 'WHERE n.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }

        $stmt = $this->db->prepare("
            SELECT n.*, u.email, u.nombres, u.apellidos
            FROM notificaciones n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            $where
            ORDER BY n.created_at DESC
            LIMIT 300
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, u.email, u.nombres, u.apellidos
            FROM notificaciones n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            WHERE n.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO notificaciones (
                usuario_id, alerta_id, canal, titulo, mensaje, prioridad, estado, url_accion, created_at
            ) VALUES (
                :usuario_id, :alerta_id, :canal, :titulo, :mensaje, :prioridad, :estado, :url_accion, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function marcarLeida(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE notificaciones
            SET estado = 'leida',
                leida_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public function pendientesUsuario(int $usuarioId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM notificaciones
            WHERE usuario_id = :usuario_id
              AND estado IN ('pendiente','enviada')
        ");
        $stmt->execute(['usuario_id' => $usuarioId]);

        return (int) $stmt->fetchColumn();
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificaciones WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);

        return (int) $stmt->fetchColumn();
    }
}
