<?php

namespace App\Services\Signature;

use App\Services\Database;

class DocumentHashService
{
    public function hashFormato(int $formatoRegistroId): array
    {
        $stmt = Database::connection()->prepare("
            SELECT fr.*, tf.codigo AS tipo_codigo, tf.nombre AS tipo_nombre,
                   g.sigla AS granja_sigla,
                   ga.codigo AS galpon_codigo,
                   c.codigo AS ciclo_codigo
            FROM formatos_registros fr
            INNER JOIN tipos_formato tf ON tf.id = fr.tipo_formato_id
            INNER JOIN granjas g ON g.id = fr.granja_id
            LEFT JOIN galpones ga ON ga.id = fr.galpon_id
            LEFT JOIN ciclos_productivos c ON c.id = fr.ciclo_id
            WHERE fr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $formatoRegistroId]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'ok' => false,
                'message' => 'Formato no encontrado.',
                'hash' => null,
                'canonical' => null,
            ];
        }

        $canonical = [
            'id' => (int) $row['id'],
            'codigo_consecutivo' => $row['codigo_consecutivo'],
            'tipo_codigo' => $row['tipo_codigo'],
            'tipo_nombre' => $row['tipo_nombre'],
            'granja' => $row['granja_sigla'],
            'galpon' => $row['galpon_codigo'],
            'ciclo' => $row['ciclo_codigo'],
            'fecha_formato' => $row['fecha_formato'],
            'datos_json' => json_decode($row['datos_json'] ?? '{}', true),
            'observaciones' => $row['observaciones'],
            'es_no_aplica' => (int) $row['es_no_aplica'],
            'motivo_no_aplica' => $row['motivo_no_aplica'],
        ];

        $json = json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'ok' => true,
            'hash' => hash('sha256', $json),
            'canonical' => $canonical,
        ];
    }

    public function hashDocumento(int $documentoId): array
    {
        $stmt = Database::connection()->prepare("
            SELECT *
            FROM documentos
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $documentoId]);
        $doc = $stmt->fetch();

        if (!$doc) {
            return [
                'ok' => false,
                'message' => 'Documento no encontrado.',
                'hash' => null,
            ];
        }

        $path = dirname(__DIR__, 3) . '/public' . $doc['archivo_path'];
        if (!file_exists($path)) {
            return [
                'ok' => false,
                'message' => 'Archivo físico no encontrado.',
                'hash' => null,
            ];
        }

        return [
            'ok' => true,
            'hash' => hash_file('sha256', $path),
            'canonical' => [
                'id' => (int) $doc['id'],
                'titulo' => $doc['titulo'],
                'archivo_path' => $doc['archivo_path'],
                'version' => (int) $doc['version'],
                'hash_file' => hash_file('sha256', $path),
            ],
        ];
    }
}
