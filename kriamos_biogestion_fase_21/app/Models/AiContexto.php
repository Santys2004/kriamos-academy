<?php

namespace App\Models;

class AiContexto extends BaseModel
{
    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(titulo LIKE :q OR contenido_resumen LIKE :q OR palabras_clave LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['tipo'])) {
            $where[] = 'tipo = :tipo';
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'estado = :estado';
            $params['estado'] = $filters['estado'];
        }

        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT c.*, u.email AS creado_por_email
            FROM ai_contextos c
            LEFT JOIN usuarios u ON u.id = c.creado_por
            $sqlWhere
            ORDER BY c.updated_at DESC, c.created_at DESC
            LIMIT 500
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM ai_contextos
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_contextos (
                tipo, referencia_id, titulo, contenido_resumen, contenido_texto,
                palabras_clave, estado, sensibilidad, creado_por, created_at, updated_at
            ) VALUES (
                :tipo, :referencia_id, :titulo, :contenido_resumen, :contenido_texto,
                :palabras_clave, :estado, :sensibilidad, :creado_por, NOW(), NOW()
            )
        ");
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateTexto(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE ai_contextos
            SET titulo = :titulo,
                contenido_resumen = :contenido_resumen,
                contenido_texto = :contenido_texto,
                palabras_clave = :palabras_clave,
                estado = :estado,
                sensibilidad = :sensibilidad,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute($data);
    }

    public function searchRelevant(string $query, int $limit = 6): array
    {
        $tokens = $this->tokens($query);

        if (empty($tokens)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($tokens as $i => $token) {
            $conditions[] = "(titulo LIKE :t$i OR contenido_resumen LIKE :t$i OR contenido_texto LIKE :t$i OR palabras_clave LIKE :t$i)";
            $params["t$i"] = '%' . $token . '%';
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM ai_contextos
            WHERE estado = 'activo'
              AND (" . implode(' OR ', $conditions) . ")
            ORDER BY
                CASE sensibilidad
                    WHEN 'publica' THEN 1
                    WHEN 'interna' THEN 2
                    WHEN 'confidencial' THEN 3
                    ELSE 4
                END ASC,
                updated_at DESC
            LIMIT :limit
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM ai_contextos")->fetchColumn();
    }

    public function countByTipo(string $tipo): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_contextos WHERE tipo = :tipo");
        $stmt->execute(['tipo' => $tipo]);

        return (int) $stmt->fetchColumn();
    }

    private function tokens(string $text): array
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^a-záéíóúñ0-9 ]/iu', ' ', $text);
        $parts = preg_split('/\s+/', $text);

        $stop = ['como','cómo','que','qué','para','por','con','una','uno','del','las','los','de','la','el','en','y','o','un','al','se','me','los','sus','son'];
        $tokens = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) >= 3 && !in_array($part, $stop, true)) {
                $tokens[] = $part;
            }
        }

        return array_values(array_unique(array_slice($tokens, 0, 10)));
    }
}
