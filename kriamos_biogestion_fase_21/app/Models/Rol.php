<?php

namespace App\Models;

class Rol extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT r.*,
                   COUNT(DISTINCT u.id) AS total_usuarios,
                   COUNT(DISTINCT rp.permiso_id) AS total_permisos
            FROM roles r
            LEFT JOIN usuarios u ON u.rol_id = r.id
            LEFT JOIN rol_permiso rp ON rp.rol_id = r.id
            GROUP BY r.id
            ORDER BY r.nombre ASC
        ");
        return $stmt->fetchAll();
    }

    public function active(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function permissions(int $rolId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, CASE WHEN rp.rol_id IS NULL THEN 0 ELSE 1 END AS asignado
            FROM permisos p
            LEFT JOIN rol_permiso rp ON rp.permiso_id = p.id AND rp.rol_id = :rol_id
            ORDER BY p.modulo ASC, p.accion ASC
        ");
        $stmt->execute(['rol_id' => $rolId]);
        return $stmt->fetchAll();
    }

    public function syncPermissions(int $rolId, array $permissionIds): void
    {
        $this->db->beginTransaction();

        $delete = $this->db->prepare("DELETE FROM rol_permiso WHERE rol_id = :rol_id");
        $delete->execute(['rol_id' => $rolId]);

        $insert = $this->db->prepare("INSERT INTO rol_permiso (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)");

        foreach ($permissionIds as $permissionId) {
            $insert->execute([
                'rol_id' => $rolId,
                'permiso_id' => (int) $permissionId,
            ]);
        }

        $this->db->commit();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO roles (nombre, descripcion, activo)
            VALUES (:nombre, :descripcion, :activo)
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE roles
            SET nombre = :nombre,
                descripcion = :descripcion,
                activo = :activo
            WHERE id = :id
        ");
        $stmt->execute($data);
    }
}
