<?php

namespace App\Models;

class Certificacion extends BaseModel
{
    public function proximasVencer(int $dias = 90): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM certificaciones_granja
            WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
        ");
        $stmt->bindValue('dias', $dias, \PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function candidatesForAlerts(array $days): array
    {
        $max = max($days);
        $stmt = $this->db->prepare("
            SELECT c.*, g.nombre AS granja_nombre, g.sigla AS granja_sigla
            FROM certificaciones_granja c
            INNER JOIN granjas g ON g.id = c.granja_id
            WHERE c.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :max_days DAY)
              AND c.estado IN ('vigente','proxima_vencer','en_renovacion')
            ORDER BY c.fecha_vencimiento ASC
        ");
        $stmt->bindValue('max_days', $max, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
