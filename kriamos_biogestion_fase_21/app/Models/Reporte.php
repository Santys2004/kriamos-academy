<?php

namespace App\Models;

class Reporte extends BaseModel
{
    public function formatos(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['granja_id'])) {
            $where[] = 'fr.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }
        if (!empty($filters['tipo_formato_id'])) {
            $where[] = 'fr.tipo_formato_id = :tipo_formato_id';
            $params['tipo_formato_id'] = (int) $filters['tipo_formato_id'];
        }
        if (!empty($filters['estado_documental_id'])) {
            $where[] = 'fr.estado_documental_id = :estado_documental_id';
            $params['estado_documental_id'] = (int) $filters['estado_documental_id'];
        }
        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'fr.fecha_formato >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $where[] = 'fr.fecha_formato <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT fr.*, tf.nombre AS tipo_nombre, tf.codigo AS tipo_codigo,
                   g.nombre AS granja_nombre, g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo,
                   c.codigo AS ciclo_codigo,
                   ed.nombre AS estado_nombre, ed.codigo AS estado_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            LEFT JOIN galpones ga ON ga.id = fr.galpon_id
            LEFT JOIN ciclos_productivos c ON c.id = fr.ciclo_id
            INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            INNER JOIN usuarios u ON u.id = fr.usuario_id
            $sqlWhere
            ORDER BY fr.fecha_formato DESC, fr.created_at DESC
            LIMIT 2000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function resumenPorGranja(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'fr.fecha_formato >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $where[] = 'fr.fecha_formato <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }
        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT g.sigla, g.nombre,
                   COUNT(fr.id) AS total_registros,
                   SUM(CASE WHEN fr.es_no_aplica = 1 THEN 1 ELSE 0 END) AS no_aplica,
                   SUM(CASE WHEN ed.codigo IN ('APROBADO','CERRADO') THEN 1 ELSE 0 END) AS cerrados,
                   SUM(CASE WHEN ed.codigo IN ('DILIGENCIADO','EN_REVISION','PENDIENTE_FIRMA') THEN 1 ELSE 0 END) AS pendientes
            FROM granjas g
            LEFT JOIN formatos_registros fr ON fr.granja_id = g.id
            LEFT JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            $sqlWhere
            GROUP BY g.id
            ORDER BY g.sigla ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function alertas(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['granja_id'])) {
            $where[] = 'a.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
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
            SELECT a.*, g.sigla AS granja_sigla, ga.codigo AS galpon_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos
            FROM alertas a
            LEFT JOIN granjas g ON g.id = a.granja_id
            LEFT JOIN galpones ga ON ga.id = a.galpon_id
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            $sqlWhere
            ORDER BY a.created_at DESC
            LIMIT 2000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function certificaciones(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, g.nombre AS granja_nombre, g.sigla AS granja_sigla
            FROM certificaciones_granja c
            INNER JOIN granjas g ON g.id = c.granja_id
            ORDER BY c.fecha_vencimiento ASC
        ");
        return $stmt->fetchAll();
    }

    public function incidencias(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['fecha_inicio'])) {
            $where[] = 'DATE(i.created_at) >= :fecha_inicio';
            $params['fecha_inicio'] = $filters['fecha_inicio'];
        }
        if (!empty($filters['fecha_fin'])) {
            $where[] = 'DATE(i.created_at) <= :fecha_fin';
            $params['fecha_fin'] = $filters['fecha_fin'];
        }
        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT i.*, fr.codigo_consecutivo, g.sigla AS granja_sigla, tf.nombre AS tipo_formato
            FROM incidencias_calidad i
            INNER JOIN formatos_registros fr ON fr.id = i.formato_registro_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            $sqlWhere
            ORDER BY i.created_at DESC
            LIMIT 2000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function registrarHistorial(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO reportes_historial (usuario_id, tipo_reporte, filtros_json, formato_salida, archivo_path, created_at)
            VALUES (:usuario_id, :tipo_reporte, :filtros_json, :formato_salida, :archivo_path, NOW())
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
}
