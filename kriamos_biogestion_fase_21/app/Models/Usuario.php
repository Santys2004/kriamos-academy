<?php

namespace App\Models;

class Usuario extends BaseModel
{
    public function byRoleNames(array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roleNames), '?'));

        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.estado = 'activo'
              AND r.nombre IN ($placeholders)
            ORDER BY r.nombre ASC, u.nombres ASC
        ");

        $stmt->execute($roleNames);
        return $stmt->fetchAll();
    }

    public function firstByRoleNames(array $roleNames): ?array
    {
        $users = $this->byRoleNames($roleNames);
        return $users[0] ?? null;
    }
}
