<?php

namespace App\Models;

class Documento extends BaseModel
{
    public function search(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(d.titulo LIKE :q OR d.descripcion LIKE :q OR d.extension LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['categoria_documental_id'])) {
            $where[] = 'd.categoria_documental_id = :categoria_documental_id';
            $params['categoria_documental_id'] = (int) $filters['categoria_documental_id'];
        }

        if (!empty($filters['granja_id'])) {
            $where[] = 'd.granja_id = :granja_id';
            $params['granja_id'] = (int) $filters['granja_id'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'd.estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT d.*, cd.nombre AS categoria_nombre, cd.codigo AS categoria_codigo,
                   g.sigla AS granja_sigla, g.nombre AS granja_nombre,
                   ga.codigo AS galpon_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email,
                   (
                       SELECT COUNT(*)
                       FROM documento_versiones dv
                       WHERE dv.documento_id = d.id
                   ) AS total_versiones
            FROM documentos d
            INNER JOIN categorias_documentales cd ON cd.id = d.categoria_documental_id
            LEFT JOIN granjas g ON g.id = d.granja_id
            LEFT JOIN galpones ga ON ga.id = d.galpon_id
            INNER JOIN usuarios u ON u.id = d.usuario_id
            $sqlWhere
            ORDER BY d.updated_at DESC, d.created_at DESC
            LIMIT 1000
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, cd.nombre AS categoria_nombre, cd.codigo AS categoria_codigo,
                   g.sigla AS granja_sigla, g.nombre AS granja_nombre,
                   ga.codigo AS galpon_codigo,
                   u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email
            FROM documentos d
            INNER JOIN categorias_documentales cd ON cd.id = d.categoria_documental_id
            LEFT JOIN granjas g ON g.id = d.granja_id
            LEFT JOIN galpones ga ON ga.id = d.galpon_id
            INNER JOIN usuarios u ON u.id = d.usuario_id
            WHERE d.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO documentos (
                categoria_documental_id, granja_id, galpon_id, usuario_id,
                titulo, descripcion, archivo_path, mime_type, extension, peso_bytes,
                version, estado
            ) VALUES (
                :categoria_documental_id, :granja_id, :galpon_id, :usuario_id,
                :titulo, :descripcion, :archivo_path, :mime_type, :extension, :peso_bytes,
                :version, :estado
            )
        ");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function updateMetadata(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE documentos
            SET categoria_documental_id = :categoria_documental_id,
                granja_id = :granja_id,
                galpon_id = :galpon_id,
                titulo = :titulo,
                descripcion = :descripcion,
                estado = :estado
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function updateFile(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE documentos
            SET archivo_path = :archivo_path,
                mime_type = :mime_type,
                extension = :extension,
                peso_bytes = :peso_bytes,
                version = version + 1,
                estado = :estado
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM documentos WHERE estado = :estado");
        $stmt->execute(['estado' => $estado]);
        return (int) $stmt->fetchColumn();
    }
}
