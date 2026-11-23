<?php

namespace App\Services\BI;

use App\Models\BiIndicador;

class BiDashboardService
{
    public function build(array $filters): array
    {
        $model = new BiIndicador();

        return [
            'resumen' => $model->resumen($filters),
            'por_granja' => $model->porGranja($filters),
            'formatos_por_dia' => $model->formatosPorDia($filters),
            'alertas_por_prioridad' => $model->alertasPorPrioridad($filters),
            'incidencias_por_nivel' => $model->incidenciasPorNivel($filters),
            'mortalidad' => $model->mortalidadConsolidada($filters),
            'certificaciones' => $model->certificacionesResumen(),
            'uso_sistema' => $model->usoSistema($filters),
        ];
    }

    public function scoreCumplimiento(array $resumen): string
    {
        $tasa = (float) ($resumen['tasa_complecion'] ?? 0);
        $atrasados = (int) ($resumen['formatos_atrasados'] ?? 0);

        if ($tasa >= 95 && $atrasados === 0) {
            return 'Excelente';
        }

        if ($tasa >= 85 && $atrasados <= 3) {
            return 'Bueno';
        }

        if ($tasa >= 70) {
            return 'En observación';
        }

        return 'Crítico';
    }
}
