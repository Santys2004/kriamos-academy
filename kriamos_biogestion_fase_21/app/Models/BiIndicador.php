<?php

namespace App\Models;

class BiIndicador extends BaseModel
{
    public function resumen(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $filters['fecha_fin'] ?? date('Y-m-d');
        $granjaId = $filters['granja_id'] ?? '';

        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ];

        $granjaWhere = '';
        if ($granjaId !== '') {
            $granjaWhere = ' AND fr.granja_id = :granja_id';
            $params['granja_id'] = (int) $granjaId;
        }

        $stmt = $this->db->prepare("
            SELECT
                COUNT(fr.id) AS total_formatos,
                SUM(CASE WHEN ed.codigo IN ('APROBADO','CERRADO') THEN 1 ELSE 0 END) AS formatos_cerrados,
                SUM(CASE WHEN ed.codigo IN ('DILIGENCIADO','EN_REVISION','PENDIENTE_FIRMA') THEN 1 ELSE 0 END) AS formatos_pendientes,
                SUM(CASE WHEN fr.es_no_aplica = 1 THEN 1 ELSE 0 END) AS no_aplica,
                SUM(CASE WHEN fr.fecha_formato <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
                         AND ed.codigo NOT IN ('APROBADO','CERRADO','NO_APLICA')
                    THEN 1 ELSE 0 END) AS formatos_atrasados
            FROM formatos_registros fr
            INNER JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            WHERE fr.fecha_formato BETWEEN :fecha_inicio AND :fecha_fin
            $granjaWhere
        ");
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        $total = (int) ($row['total_formatos'] ?? 0);
        $cerrados = (int) ($row['formatos_cerrados'] ?? 0);

        $row['tasa_complecion'] = $total > 0 ? round(($cerrados / $total) * 100, 2) : 0;

        return $row;
    }

    public function porGranja(array $filters = []): array
    {
        $fechaInicio = $filters['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $filters['fecha_fin'] ?? date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT
                g.id,
                g.sigla,
                g.nombre,
                COUNT(fr.id) AS total_formatos,
                SUM(CASE WHEN ed.codigo IN ('APROBADO','CERRADO') THEN 1 ELSE 0 END) AS cerrados,
                SUM(CASE WHEN ed.codigo IN ('DILIGENCIADO','EN_REVISION','PENDIENTE_FIRMA') THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN fr.es_no_aplica = 1 THEN 1 ELSE 0 END) AS no_aplica,
                SUM(CASE WHEN fr.fecha_formato <= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
                         AND ed.codigo NOT IN ('APROBADO','CERRADO','NO_APLICA')
                    THEN 1 ELSE 0 END) AS atrasados
            FROM granjas g
            LEFT JOIN formatos_registros fr ON fr.granja_id = g.id
                AND fr.fecha_formato BETWEEN :fecha_inicio AND :fecha_fin
            LEFT JOIN estados_documentales ed ON ed.id = fr.estado_documental_id
            GROUP BY g.id
            ORDER BY g.sigla ASC
        ");
        $stmt->execute([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $total = (int) ($row['total_formatos'] ?? 0);
            $cerrados = (int) ($row['cerrados'] ?? 0);
            $row['tasa_complecion'] = $total > 0 ? round(($cerrados / $total) * 100, 2) : 0;
        }

        return $rows;
    }

    public function formatosPorDia(array $filters = []): array
    {
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $filters['fecha_fin'] ?? date('Y-m-d'),
        ];

        $where = '';
        if (!empty($filters['granja_id'])) {
            $where = ' AND fr.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }

        $stmt = $this->db->prepare("
            SELECT fr.fecha_formato AS fecha, COUNT(*) AS total
            FROM formatos_registros fr
            WHERE fr.fecha_formato BETWEEN :fecha_inicio AND :fecha_fin
            $where
            GROUP BY fr.fecha_formato
            ORDER BY fr.fecha_formato ASC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function alertasPorPrioridad(array $filters = []): array
    {
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $filters['fecha_fin'] ?? date('Y-m-d'),
        ];

        $where = '';
        if (!empty($filters['granja_id'])) {
            $where = ' AND a.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }

        $stmt = $this->db->prepare("
            SELECT a.prioridad, COUNT(*) AS total
            FROM alertas a
            WHERE DATE(a.created_at) BETWEEN :fecha_inicio AND :fecha_fin
            $where
            GROUP BY a.prioridad
            ORDER BY FIELD(a.prioridad, 'critica','alta','media','baja')
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function incidenciasPorNivel(array $filters = []): array
    {
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $filters['fecha_fin'] ?? date('Y-m-d'),
        ];

        $stmt = $this->db->prepare("
            SELECT i.nivel, COUNT(*) AS total
            FROM incidencias_calidad i
            WHERE DATE(i.created_at) BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY i.nivel
            ORDER BY FIELD(i.nivel, 'critica','alta','media','baja')
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function mortalidadConsolidada(array $filters = []): array
    {
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $filters['fecha_fin'] ?? date('Y-m-d'),
        ];

        $where = '';
        if (!empty($filters['granja_id'])) {
            $where = ' AND fr.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }

        $stmt = $this->db->prepare("
            SELECT
                g.sigla AS granja,
                fr.fecha_formato AS fecha,
                SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(fr.datos_json, '$.aves_muertas')) AS UNSIGNED)) AS aves_muertas
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            WHERE tf.codigo = 'MORTALIDAD_DIARIA'
              AND fr.fecha_formato BETWEEN :fecha_inicio AND :fecha_fin
              $where
            GROUP BY g.id, fr.fecha_formato
            ORDER BY fr.fecha_formato ASC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function certificacionesResumen(): array
    {
        $stmt = $this->db->query("
            SELECT
                SUM(CASE WHEN fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) AS vencidas,
                SUM(CASE WHEN fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS vence_30,
                SUM(CASE WHEN fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) THEN 1 ELSE 0 END) AS vence_60,
                SUM(CASE WHEN estado = 'vigente' THEN 1 ELSE 0 END) AS vigentes
            FROM certificaciones_granja
        ");

        return $stmt->fetch() ?: [];
    }

    public function usoSistema(array $filters = []): array
    {
        $params = [
            'fecha_inicio' => $filters['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $filters['fecha_fin'] ?? date('Y-m-d'),
        ];

        $stmt = $this->db->prepare("
            SELECT
                DATE(a.created_at) AS fecha,
                COUNT(*) AS total_acciones,
                COUNT(DISTINCT a.usuario_id) AS usuarios_activos
            FROM auditoria a
            WHERE DATE(a.created_at) BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE(a.created_at)
            ORDER BY fecha ASC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function createSnapshot(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO bi_snapshots (
                fecha_snapshot, usuario_id, filtros_json, indicadores_json, created_at
            ) VALUES (
                :fecha_snapshot, :usuario_id, :filtros_json, :indicadores_json, NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function snapshots(): array
    {
        $stmt = $this->db->query("
            SELECT s.*, u.email
            FROM bi_snapshots s
            LEFT JOIN usuarios u ON u.id = s.usuario_id
            ORDER BY s.created_at DESC
            LIMIT 100
        ");

        return $stmt->fetchAll();
    }
}
