<?php

namespace App\Services\IcaAudit;

use App\Models\IcaMatrizDocumento;
use App\Services\Database;

class IcaMatrixBuilderService
{
    public function buildDefaultMatrix(int $expedienteId, int $granjaId): array
    {
        $items = [
            ['Bioseguridad', 'POE vigente de bioseguridad', 'Documento interno que establece procedimientos de bioseguridad.', 'documento'],
            ['Sanidad', 'Registros sanitarios del ciclo', 'Formatos sanitarios y controles asociados al ciclo productivo.', 'formato'],
            ['Certificaciones', 'Certificación de granja avícola', 'Certificación vigente y soporte de vencimiento.', 'certificacion'],
            ['Mortalidad', 'Registro diario de mortalidad', 'Formato de mortalidad diligenciado y firmado.', 'formato'],
            ['Limpieza y desinfección', 'Registros de limpieza y desinfección', 'Evidencia documental de limpieza y desinfección de galpones.', 'formato'],
            ['Control de plagas', 'Control integrado de plagas', 'Soportes del programa de control de plagas.', 'documento'],
            ['Agua', 'Calidad y tratamiento de agua', 'Registros o soportes de manejo de agua.', 'documento'],
            ['Alimento', 'Trazabilidad de alimento', 'Registros y soportes de recepción/uso de alimento.', 'formato'],
            ['Medicamentos', 'Uso de medicamentos o biológicos', 'Registros asociados a tratamientos cuando aplique.', 'formato'],
            ['Personal', 'Capacitación y dotación', 'Soportes de capacitación, inducción y elementos de protección.', 'documento'],
        ];

        $created = 0;
        $model = new IcaMatrizDocumento();

        foreach ($items as $item) {
            [$categoria, $requisito, $descripcion, $tipo] = $item;

            $documentoId = null;
            $tipoFormatoId = null;
            $certificacionId = null;

            if ($tipo === 'documento') {
                $documentoId = $this->findDocumento($granjaId, $requisito);
            }

            if ($tipo === 'formato') {
                $tipoFormatoId = $this->findTipoFormato($requisito);
            }

            if ($tipo === 'certificacion') {
                $certificacionId = $this->findCertificacion($granjaId);
            }

            $model->create([
                'expediente_id' => $expedienteId,
                'categoria' => $categoria,
                'requisito' => $requisito,
                'descripcion' => $descripcion,
                'documento_id' => $documentoId,
                'tipo_formato_id' => $tipoFormatoId,
                'certificacion_id' => $certificacionId,
                'obligatorio' => 1,
                'estado_cumplimiento' => ($documentoId || $tipoFormatoId || $certificacionId) ? 'pendiente_revision' : 'pendiente',
                'observacion' => null,
            ]);

            $created++;
        }

        return [
            'created' => $created,
        ];
    }

    private function findDocumento(int $granjaId, string $text): ?int
    {
        $stmt = Database::connection()->prepare("
            SELECT id
            FROM documentos
            WHERE granja_id = :granja_id
              AND estado IN ('vigente','en_revision')
              AND (titulo LIKE :q OR descripcion LIKE :q)
            ORDER BY updated_at DESC
            LIMIT 1
        ");
        $stmt->execute([
            'granja_id' => $granjaId,
            'q' => '%' . mb_substr($text, 0, 20) . '%',
        ]);

        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function findTipoFormato(string $text): ?int
    {
        $stmt = Database::connection()->prepare("
            SELECT id
            FROM tipos_formato
            WHERE activo = 1
              AND (nombre LIKE :q OR descripcion LIKE :q)
            ORDER BY nombre ASC
            LIMIT 1
        ");
        $stmt->execute([
            'q' => '%' . mb_substr($text, 0, 20) . '%',
        ]);

        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }

    private function findCertificacion(int $granjaId): ?int
    {
        $stmt = Database::connection()->prepare("
            SELECT id
            FROM certificaciones_granja
            WHERE granja_id = :granja_id
            ORDER BY fecha_vencimiento DESC
            LIMIT 1
        ");
        $stmt->execute(['granja_id' => $granjaId]);

        $id = $stmt->fetchColumn();

        return $id ? (int) $id : null;
    }
}
