<?php

namespace App\Models;

class FormatoRegistro extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT fr.*, tf.nombre AS tipo_nombre, tf.codigo AS tipo_codigo,
                   g.nombre AS granja_nombre, g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo,
                   c.codigo AS ciclo_codigo,
                   ed.nombre AS estado_nombre, ed.codigo AS estado_codigo, ed.color AS estado_color,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            LEFT JOIN galpones ga ON ga.id = fr.galpon_id
            LEFT JOIN ciclos_productivos c ON c.id = fr.ciclo_id
            INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            INNER JOIN usuarios u ON u.id = fr.usuario_id
            ORDER BY fr.created_at DESC
            LIMIT 300
        ");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT fr.*, tf.nombre AS tipo_nombre, tf.codigo AS tipo_codigo, tf.descripcion AS tipo_descripcion,
                   tf.fundamento, tf.frecuencia, tf.permite_no_aplica,
                   g.nombre AS granja_nombre, g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo,
                   c.codigo AS ciclo_codigo,
                   ed.nombre AS estado_nombre, ed.codigo AS estado_codigo, ed.color AS estado_color,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            LEFT JOIN galpones ga ON ga.id = fr.galpon_id
            LEFT JOIN ciclos_productivos c ON c.id = fr.ciclo_id
            INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            INNER JOIN usuarios u ON u.id = fr.usuario_id
            WHERE fr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row && isset($row['datos_json'])) {
            $row['datos'] = json_decode($row['datos_json'], true) ?: [];
        }

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO formatos_registros (
                tipo_formato_id, granja_id, galpon_id, ciclo_id, usuario_id,
                estado_documental_id, codigo_consecutivo, fecha_formato, hora_formato,
                es_no_aplica, motivo_no_aplica, datos_json, observaciones,
                hash_documento, version, origen_registro, sincronizado, diligenciado_at
            ) VALUES (
                :tipo_formato_id, :granja_id, :galpon_id, :ciclo_id, :usuario_id,
                :estado_documental_id, :codigo_consecutivo, :fecha_formato, :hora_formato,
                :es_no_aplica, :motivo_no_aplica, :datos_json, :observaciones,
                :hash_documento, :version, :origen_registro, :sincronizado, NOW()
            )
        ");

        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function nextConsecutivo(string $sigla, string $tipoCodigo): string
    {
        $prefix = $sigla . '-' . $tipoCodigo . '-' . date('Ymd');

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM formatos_registros
            WHERE codigo_consecutivo LIKE :prefix
        ");
        $stmt->execute(['prefix' => $prefix . '%']);
        $next = ((int) $stmt->fetchColumn()) + 1;

        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }


    public function duplicadoExiste(int $tipoFormatoId, int $granjaId, ?int $galponId, ?int $cicloId, string $fechaFormato): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM formatos_registros
            WHERE tipo_formato_id = :tipo_formato_id
              AND granja_id = :granja_id
              AND (galpon_id <=> :galpon_id)
              AND (ciclo_id <=> :ciclo_id)
              AND fecha_formato = :fecha_formato
              AND es_no_aplica = 0
        ");

        $stmt->execute([
            'tipo_formato_id' => $tipoFormatoId,
            'granja_id' => $granjaId,
            'galpon_id' => $galponId,
            'ciclo_id' => $cicloId,
            'fecha_formato' => $fechaFormato,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function aprobarConFirma(int $id, int $estadoAprobadoId): void
    {
        $stmt = $this->db->prepare("
            UPDATE formatos_registros
            SET estado_documental_id = :estado_id,
                aprobado_at = NOW(),
                cerrado_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'estado_id' => $estadoAprobadoId,
            'id' => $id,
        ]);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM formatos_registros")->fetchColumn();
    }

    public function countToday(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM formatos_registros WHERE fecha_formato = CURDATE()")->fetchColumn();
    }

    public function countNoAplica(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM formatos_registros WHERE es_no_aplica = 1")->fetchColumn();
    }
}
