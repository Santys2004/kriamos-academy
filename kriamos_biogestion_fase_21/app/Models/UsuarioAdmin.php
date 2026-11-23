<?php

namespace App\Models;

class UsuarioAdmin extends BaseModel
{
    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(u.nombres LIKE :q OR u.apellidos LIKE :q OR u.email LIKE :q OR u.cargo LIKE :q)";
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['rol_id'])) {
            $where[] = "u.rol_id = :rol_id";
            $params['rol_id'] = (int) $filters['rol_id'];
        }

        if (!empty($filters['estado'])) {
            $where[] = "u.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            $sqlWhere
            ORDER BY u.nombres ASC, u.apellidos ASC
            LIMIT 500
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (
                rol_id, nombres, apellidos, email, password_hash, cargo,
                telefono, estado, debe_cambiar_password, password_changed_at
            ) VALUES (
                :rol_id, :nombres, :apellidos, :email, :password_hash, :cargo,
                :telefono, :estado, :debe_cambiar_password, NOW()
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET rol_id = :rol_id,
                nombres = :nombres,
                apellidos = :apellidos,
                email = :email,
                cargo = :cargo,
                telefono = :telefono,
                estado = :estado
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function resetPassword(int $id, string $passwordHash): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET password_hash = :password_hash,
                debe_cambiar_password = 1,
                password_changed_at = NOW(),
                intentos_fallidos = 0
            WHERE id = :id
        ");
        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $id,
        ]);
    }

    public function changeEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET estado = :estado
            WHERE id = :id
        ");
        $stmt->execute([
            'estado' => $estado,
            'id' => $id,
        ]);
    }
}
