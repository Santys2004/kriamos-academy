<?php

namespace App\Models;

class Granja extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT g.*,
                   COUNT(DISTINCT ga.id) AS galpones_registrados,
                   COUNT(DISTINCT c.id) AS ciclos_activos
            FROM granjas g
            LEFT JOIN galpones ga ON ga.granja_id = g.id
            LEFT JOIN ciclos_productivos c ON c.granja_id = g.id AND c.estado = 'activo'
            GROUP BY g.id
            ORDER BY g.nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM granjas WHERE estado = 'activa' ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM granjas WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM granjas")->fetchColumn();
    }

    public function capacidadTotal(): int
    {
        return (int) $this->db->query("SELECT COALESCE(SUM(capacidad_pollos), 0) FROM granjas")->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO granjas (
                nombre, sigla, codigo_interno, ubicacion, municipio, departamento,
                capacidad_pollos, numero_galpones, estado, observaciones
            ) VALUES (
                :nombre, :sigla, :codigo_interno, :ubicacion, :municipio, :departamento,
                :capacidad_pollos, :numero_galpones, :estado, :observaciones
            )
        ");

        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->db->prepare("
            UPDATE granjas
            SET nombre = :nombre,
                sigla = :sigla,
                codigo_interno = :codigo_interno,
                ubicacion = :ubicacion,
                municipio = :municipio,
                departamento = :departamento,
                capacidad_pollos = :capacidad_pollos,
                numero_galpones = :numero_galpones,
                estado = :estado,
                observaciones = :observaciones
            WHERE id = :id
        ");

        $stmt->execute($data);
    }
}
