<?php

namespace App\Models;

class CicloProductivo extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, g.nombre AS granja_nombre, g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo, ga.numero AS galpon_numero,
                   f.nombre AS fase_nombre, f.codigo AS fase_codigo
            FROM ciclos_productivos c
            INNER JOIN granjas g ON g.id = c.granja_id
            INNER JOIN galpones ga ON ga.id = c.galpon_id
            INNER JOIN fases_ciclo f ON f.id = c.fase_ciclo_id
            ORDER BY c.created_at DESC, g.sigla ASC, ga.numero ASC
        ");
        return $stmt->fetchAll();
    }

    public function byGranja(int $granjaId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, ga.codigo AS galpon_codigo, ga.numero AS galpon_numero,
                   f.nombre AS fase_nombre, f.codigo AS fase_codigo
            FROM ciclos_productivos c
            INNER JOIN galpones ga ON ga.id = c.galpon_id
            INNER JOIN fases_ciclo f ON f.id = c.fase_ciclo_id
            WHERE c.granja_id = :granja_id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute(['granja_id' => $granjaId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ciclos_productivos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function countActive(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ciclos_productivos WHERE estado = 'activo'")->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ciclos_productivos (
                granja_id, galpon_id, fase_ciclo_id, codigo, fecha_inicio,
                fecha_encasetamiento, fecha_estimada_salida, fecha_cierre,
                edad_aves_dias, linea_genetica, aves_recibidas, aves_actuales,
                mortalidad_acumulada, estado, observaciones
            ) VALUES (
                :granja_id, :galpon_id, :fase_ciclo_id, :codigo, :fecha_inicio,
                :fecha_encasetamiento, :fecha_estimada_salida, :fecha_cierre,
                :edad_aves_dias, :linea_genetica, :aves_recibidas, :aves_actuales,
                :mortalidad_acumulada, :estado, :observaciones
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->db->prepare("
            UPDATE ciclos_productivos
            SET granja_id = :granja_id,
                galpon_id = :galpon_id,
                fase_ciclo_id = :fase_ciclo_id,
                codigo = :codigo,
                fecha_inicio = :fecha_inicio,
                fecha_encasetamiento = :fecha_encasetamiento,
                fecha_estimada_salida = :fecha_estimada_salida,
                fecha_cierre = :fecha_cierre,
                edad_aves_dias = :edad_aves_dias,
                linea_genetica = :linea_genetica,
                aves_recibidas = :aves_recibidas,
                aves_actuales = :aves_actuales,
                mortalidad_acumulada = :mortalidad_acumulada,
                estado = :estado,
                observaciones = :observaciones
            WHERE id = :id
        ");
        $stmt->execute($data);
    }
}
