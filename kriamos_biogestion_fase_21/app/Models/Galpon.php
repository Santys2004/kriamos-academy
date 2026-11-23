<?php

namespace App\Models;

class Galpon extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT ga.*, g.nombre AS granja_nombre, g.sigla AS granja_sigla
            FROM galpones ga
            INNER JOIN granjas g ON g.id = ga.granja_id
            ORDER BY g.sigla ASC, ga.numero ASC
        ");
        return $stmt->fetchAll();
    }

    public function byGranja(int $granjaId): array
    {
        $stmt = $this->db->prepare("
            SELECT ga.*, g.sigla AS granja_sigla
            FROM galpones ga
            INNER JOIN granjas g ON g.id = ga.granja_id
            WHERE ga.granja_id = :granja_id
            ORDER BY ga.numero ASC
        ");
        $stmt->execute(['granja_id' => $granjaId]);
        return $stmt->fetchAll();
    }

    public function activeByGranja(int $granjaId): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM galpones
            WHERE granja_id = :granja_id AND estado IN ('activo','vacio_sanitario','mantenimiento')
            ORDER BY numero ASC
        ");
        $stmt->execute(['granja_id' => $granjaId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM galpones WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM galpones")->fetchColumn();
    }

    public function countByGranja(int $granjaId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM galpones WHERE granja_id = :granja_id");
        $stmt->execute(['granja_id' => $granjaId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO galpones (
                granja_id, codigo, numero, capacidad_pollos, area_m2, estado, observaciones
            ) VALUES (
                :granja_id, :codigo, :numero, :capacidad_pollos, :area_m2, :estado, :observaciones
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->db->prepare("
            UPDATE galpones
            SET granja_id = :granja_id,
                codigo = :codigo,
                numero = :numero,
                capacidad_pollos = :capacidad_pollos,
                area_m2 = :area_m2,
                estado = :estado,
                observaciones = :observaciones
            WHERE id = :id
        ");
        $stmt->execute($data);
    }
}
