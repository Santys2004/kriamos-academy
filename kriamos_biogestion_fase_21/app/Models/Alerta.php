<?php

namespace App\Models;

class Alerta extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT a.*, g.sigla AS granja_sigla, ga.codigo AS galpon_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos,
                   fr.codigo_consecutivo
            FROM alertas a
            LEFT JOIN granjas g ON g.id = a.granja_id
            LEFT JOIN galpones ga ON ga.id = a.galpon_id
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            LEFT JOIN formatos_registros fr ON fr.id = a.formato_registro_id
            ORDER BY
                FIELD(a.prioridad, 'critica','alta','media','baja'),
                a.created_at DESC
            LIMIT 500
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, g.nombre AS granja_nombre, g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email,
                   fr.codigo_consecutivo
            FROM alertas a
            LEFT JOIN granjas g ON g.id = a.granja_id
            LEFT JOIN galpones ga ON ga.id = a.galpon_id
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            LEFT JOIN formatos_registros fr ON fr.id = a.formato_registro_id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function pendientes(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM alertas WHERE estado IN ('pendiente','enviada')")->fetchColumn();
    }

    public function countByPriority(string $prioridad): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM alertas WHERE prioridad = :prioridad AND estado IN ('pendiente','enviada','leida')");
        $stmt->execute(['prioridad' => $prioridad]);
        return (int) $stmt->fetchColumn();
    }

    public function createIfNotExists(array $data): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM alertas
            WHERE tipo = :tipo
              AND COALESCE(formato_registro_id, 0) = COALESCE(:formato_registro_id, 0)
              AND COALESCE(certificacion_id, 0) = COALESCE(:certificacion_id, 0)
              AND COALESCE(granja_id, 0) = COALESCE(:granja_id, 0)
              AND titulo = :titulo
              AND estado IN ('pendiente','enviada','leida')
            LIMIT 1
        ");

        $stmt->execute([
            'tipo' => $data['tipo'],
            'formato_registro_id' => $data['formato_registro_id'] ?? null,
            'certificacion_id' => $data['certificacion_id'] ?? null,
            'granja_id' => $data['granja_id'] ?? null,
            'titulo' => $data['titulo'],
        ]);

        if ($stmt->fetch()) {
            return null;
        }

        $insert = $this->db->prepare("
            INSERT INTO alertas (
                usuario_id, granja_id, galpon_id, formato_registro_id, certificacion_id,
                tipo, titulo, mensaje, prioridad, estado, fecha_limite
            ) VALUES (
                :usuario_id, :granja_id, :galpon_id, :formato_registro_id, :certificacion_id,
                :tipo, :titulo, :mensaje, :prioridad, :estado, :fecha_limite
            )
        ");

        $insert->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'granja_id' => $data['granja_id'] ?? null,
            'galpon_id' => $data['galpon_id'] ?? null,
            'formato_registro_id' => $data['formato_registro_id'] ?? null,
            'certificacion_id' => $data['certificacion_id'] ?? null,
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'prioridad' => $data['prioridad'] ?? 'media',
            'estado' => $data['estado'] ?? 'pendiente',
            'fecha_limite' => $data['fecha_limite'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateEstado(int $id, string $estado): void
    {
        $fields = [
            'estado' => $estado,
            'leida_at' => $estado === 'leida' ? date('Y-m-d H:i:s') : null,
            'cerrada_at' => in_array($estado, ['atendida','cerrada'], true) ? date('Y-m-d H:i:s') : null,
        ];

        $stmt = $this->db->prepare("
            UPDATE alertas
            SET estado = :estado,
                leida_at = COALESCE(:leida_at, leida_at),
                cerrada_at = COALESCE(:cerrada_at, cerrada_at)
            WHERE id = :id
        ");

        $stmt->execute([
            'estado' => $fields['estado'],
            'leida_at' => $fields['leida_at'],
            'cerrada_at' => $fields['cerrada_at'],
            'id' => $id,
        ]);
    }

    public function recientesParaCorreo(): array
    {
        $stmt = $this->db->query("
            SELECT a.*, u.email, u.nombres, u.apellidos
            FROM alertas a
            INNER JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.estado = 'pendiente'
              AND u.email IS NOT NULL
            ORDER BY a.created_at DESC
            LIMIT 100
        ");
        return $stmt->fetchAll();
    }
}
