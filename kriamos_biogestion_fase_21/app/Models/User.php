<?php

namespace App\Models;

class User extends BaseModel
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE LOWER(u.email) = LOWER(:email)
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findWithRoleAndPermissions(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user || $user['estado'] !== 'activo') {
            return null;
        }

        $permStmt = $this->db->prepare("
            SELECT p.codigo
            FROM permisos p
            INNER JOIN rol_permiso rp ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id
        ");
        $permStmt->execute(['rol_id' => $user['rol_id']]);
        $user['permisos'] = array_column($permStmt->fetchAll(), 'codigo');

        return $user;
    }

    public function markLastLogin(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET ultimo_ingreso_at = NOW(), intentos_fallidos = 0
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public function increaseFailedAttempts(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET intentos_fallidos = intentos_fallidos + 1
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }

    public function resetFailedAttempts(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET intentos_fallidos = 0
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
    }
}
