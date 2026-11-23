<?php

namespace App\Models;

class FirmaAvanzada extends BaseModel
{
    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = 'fa.usuario_id = :usuario_id';
            $params['usuario_id'] = (int) $filters['usuario_id'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'fa.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'DATE(fa.created_at) >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $where[] = 'DATE(fa.created_at) <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT fa.*, u.email, u.nombres, u.apellidos,
                   fr.codigo_consecutivo,
                   tf.nombre AS tipo_formato,
                   g.sigla AS granja_sigla
            FROM firmas_avanzadas fa
            INNER JOIN usuarios u ON u.id = fa.usuario_id
            LEFT JOIN formatos_registros fr ON fr.id = fa.formato_registro_id
            LEFT JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            LEFT JOIN granjas g ON g.id = fr.granja_id
            $sqlWhere
            ORDER BY fa.created_at DESC
            LIMIT 1000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT fa.*, u.email, u.nombres, u.apellidos,
                   fr.codigo_consecutivo,
                   tf.nombre AS tipo_formato,
                   g.sigla AS granja_sigla
            FROM firmas_avanzadas fa
            INNER JOIN usuarios u ON u.id = fa.usuario_id
            LEFT JOIN formatos_registros fr ON fr.id = fa.formato_registro_id
            LEFT JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            LEFT JOIN granjas g ON g.id = fr.granja_id
            WHERE fa.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO firmas_avanzadas (
                usuario_id, certificado_id, formato_registro_id, documento_id,
                tipo_objeto, objeto_id, hash_original, hash_firma,
                evidencia_json, estado, ip, dispositivo, created_at
            ) VALUES (
                :usuario_id, :certificado_id, :formato_registro_id, :documento_id,
                :tipo_objeto, :objeto_id, :hash_original, :hash_firma,
                :evidencia_json, :estado, :ip, :dispositivo, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function marcarSellada(int $id, int $selloId): void
    {
        $stmt = $this->db->prepare("
            UPDATE firmas_avanzadas
            SET sello_tiempo_id = :sello_id,
                estado = 'sellada',
                sellada_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'sello_id' => $selloId,
            'id' => $id,
        ]);
    }

    public function actualizarVerificacion(int $id, string $estado, ?string $mensaje): void
    {
        $stmt = $this->db->prepare("
            UPDATE firmas_avanzadas
            SET estado_verificacion = :estado_verificacion,
                mensaje_verificacion = :mensaje_verificacion,
                verificada_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'estado_verificacion' => $estado,
            'mensaje_verificacion' => $mensaje,
            'id' => $id,
        ]);
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM firmas_avanzadas WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }
}
