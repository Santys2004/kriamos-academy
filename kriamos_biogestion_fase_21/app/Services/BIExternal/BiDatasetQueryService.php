<?php

namespace App\Services\BIExternal;

use App\Models\BiExternalDataset;
use App\Services\Database;

class BiDatasetQueryService
{
    public function query(string $codigo, array $filters = []): array
    {
        $dataset = (new BiExternalDataset())->findByCodigo($codigo);

        if (!$dataset) {
            return [
                'ok' => false,
                'message' => 'Dataset no encontrado o inactivo.',
                'rows' => [],
            ];
        }

        $view = $dataset['vista_sql'];
        $allowedViews = [
            'vw_bi_granjas',
            'vw_bi_galpones',
            'vw_bi_formatos',
            'vw_bi_alertas',
            'vw_bi_certificaciones',
            'vw_bi_documentos',
            'vw_bi_indicadores_granja',
            'vw_bi_mortalidad_diaria',
            'vw_bi_uso_sistema'
        ];

        if (!in_array($view, $allowedViews, true)) {
            return [
                'ok' => false,
                'message' => 'Vista SQL no permitida.',
                'rows' => [],
            ];
        }

        $limit = min((int) ($filters['limit'] ?? 5000), 10000);
        $sql = "SELECT * FROM {$view} LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'ok' => true,
            'dataset' => $dataset,
            'rows' => $stmt->fetchAll(),
        ];
    }
}
