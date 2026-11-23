<?php

namespace App\Services\AI;

use App\Models\AiContexto;
use App\Services\Database;

class ContextIndexerService
{
    public function indexHelpArticles(?int $userId = null): int
    {
        $db = Database::connection();
        $rows = $db->query("
            SELECT id, modulo, titulo, resumen, contenido, palabras_clave
            FROM ayuda_articulos
            WHERE activo = 1
        ")->fetchAll();

        $count = 0;
        $model = new AiContexto();

        foreach ($rows as $row) {
            $existing = $this->findExisting('ayuda', (int) $row['id']);

            $payload = [
                'tipo' => 'ayuda',
                'referencia_id' => (int) $row['id'],
                'titulo' => $row['titulo'],
                'contenido_resumen' => $row['resumen'] ?: ('Artículo de ayuda del módulo ' . $row['modulo']),
                'contenido_texto' => $row['contenido'],
                'palabras_clave' => $row['palabras_clave'],
                'estado' => 'activo',
                'sensibilidad' => 'interna',
                'creado_por' => $userId,
            ];

            if ($existing) {
                $model->updateTexto((int) $existing['id'], $payload);
            } else {
                $model->create($payload);
            }

            $count++;
        }

        return $count;
    }

    public function indexDocumentos(?int $userId = null): int
    {
        $db = Database::connection();
        $rows = $db->query("
            SELECT d.id, d.titulo, d.descripcion, d.extension, d.estado, cd.nombre AS categoria
            FROM documentos d
            INNER JOIN categorias_documentales cd ON cd.id = d.categoria_documental_id
            WHERE d.estado IN ('vigente','en_revision')
        ")->fetchAll();

        $count = 0;
        $model = new AiContexto();

        foreach ($rows as $row) {
            $existing = $this->findExisting('documento', (int) $row['id']);

            $payload = [
                'tipo' => 'documento',
                'referencia_id' => (int) $row['id'],
                'titulo' => $row['titulo'],
                'contenido_resumen' => $row['descripcion'] ?: 'Documento del núcleo documental: ' . $row['categoria'],
                'contenido_texto' => 'Documento: ' . $row['titulo'] . '. Categoría: ' . $row['categoria'] . '. Extensión: ' . $row['extension'] . '. Estado: ' . $row['estado'] . '.',
                'palabras_clave' => $row['categoria'] . ', ' . $row['extension'] . ', documento, kriamos',
                'estado' => 'activo',
                'sensibilidad' => 'interna',
                'creado_por' => $userId,
            ];

            if ($existing) {
                $model->updateTexto((int) $existing['id'], $payload);
            } else {
                $model->create($payload);
            }

            $count++;
        }

        return $count;
    }

    public function indexTiposFormato(?int $userId = null): int
    {
        $db = Database::connection();
        $rows = $db->query("
            SELECT id, codigo, nombre, descripcion, frecuencia
            FROM tipos_formato
            WHERE activo = 1
        ")->fetchAll();

        $count = 0;
        $model = new AiContexto();

        foreach ($rows as $row) {
            $existing = $this->findExisting('formato_tipo', (int) $row['id']);

            $payload = [
                'tipo' => 'formato_tipo',
                'referencia_id' => (int) $row['id'],
                'titulo' => $row['nombre'],
                'contenido_resumen' => $row['descripcion'] ?: 'Formato ICA interno de Kriamos',
                'contenido_texto' => 'Código: ' . $row['codigo'] . '. Nombre: ' . $row['nombre'] . '. Frecuencia: ' . $row['frecuencia'] . '. Descripción: ' . $row['descripcion'],
                'palabras_clave' => $row['codigo'] . ', formato, ICA, ' . $row['nombre'],
                'estado' => 'activo',
                'sensibilidad' => 'interna',
                'creado_por' => $userId,
            ];

            if ($existing) {
                $model->updateTexto((int) $existing['id'], $payload);
            } else {
                $model->create($payload);
            }

            $count++;
        }

        return $count;
    }

    public function indexAll(?int $userId = null): array
    {
        return [
            'ayuda' => $this->indexHelpArticles($userId),
            'documentos' => $this->indexDocumentos($userId),
            'tipos_formato' => $this->indexTiposFormato($userId),
        ];
    }

    private function findExisting(string $tipo, int $referenciaId): ?array
    {
        $stmt = Database::connection()->prepare("
            SELECT *
            FROM ai_contextos
            WHERE tipo = :tipo
              AND referencia_id = :referencia_id
            LIMIT 1
        ");
        $stmt->execute([
            'tipo' => $tipo,
            'referencia_id' => $referenciaId,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
