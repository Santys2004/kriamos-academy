<?php

namespace App\Models;

class AyudaArticulo extends BaseModel
{
    public function all(array $filters = []): array
    {
        $where = ['a.activo = 1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(a.titulo LIKE :q OR a.contenido LIKE :q OR a.palabras_clave LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['modulo'])) {
            $where[] = 'a.modulo = :modulo';
            $params['modulo'] = $filters['modulo'];
        }

        $sqlWhere = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT a.*
            FROM ayuda_articulos a
            $sqlWhere
            ORDER BY a.orden ASC, a.titulo ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ayuda_articulos
            WHERE id = :id AND activo = 1
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function bySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ayuda_articulos
            WHERE slug = :slug AND activo = 1
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function modules(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT modulo
            FROM ayuda_articulos
            WHERE activo = 1
            ORDER BY modulo ASC
        ");
        return array_column($stmt->fetchAll(), 'modulo');
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ayuda_articulos WHERE activo = 1")->fetchColumn();
    }
}
