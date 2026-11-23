<?php

namespace App\Services;

use App\Models\CicloProductivo;
use App\Models\FormatoRegistro;

class FormatoValidationService
{
    public function validateBeforeSave(array $context, array $datos): array
    {
        $errors = [];
        $warnings = [];

        $fechaFormato = $context['fecha_formato'] ?? date('Y-m-d');

        if ($fechaFormato > date('Y-m-d')) {
            $errors[] = 'La fecha del formato no puede ser futura.';
        }

        foreach ($datos as $campo => $valor) {
            if (is_numeric($valor) && (float) $valor < 0) {
                $errors[] = 'El campo ' . str_replace('_', ' ', $campo) . ' no puede tener valores negativos.';
            }
        }

        if (!empty($datos['fecha_vencimiento']) && $datos['fecha_vencimiento'] < date('Y-m-d')) {
            $warnings[] = 'El producto o biológico registra fecha de vencimiento anterior a la fecha actual.';
        }

        if (($context['tipo_codigo'] ?? '') === 'MORTALIDAD_DIARIA' && isset($datos['aves_muertas'])) {
            $avesMuertas = (int) $datos['aves_muertas'];
            $cicloId = (int) ($context['ciclo_id'] ?? 0);

            if ($cicloId > 0) {
                $ciclo = (new CicloProductivo())->find($cicloId);
                if ($ciclo && $avesMuertas > (int) $ciclo['aves_actuales']) {
                    $errors[] = 'La mortalidad registrada no puede ser mayor al número de aves actuales del ciclo.';
                }

                if ($ciclo && (int) $ciclo['aves_actuales'] > 0) {
                    $porcentaje = ($avesMuertas / (int) $ciclo['aves_actuales']) * 100;
                    if ($porcentaje >= 1) {
                        $warnings[] = 'La mortalidad registrada es igual o superior al 1% de las aves actuales del ciclo. Requiere revisión sanitaria.';
                    }
                }
            }
        }

        $galponId = ((int) ($context['galpon_id'] ?? 0)) ?: null;
        $cicloId = ((int) ($context['ciclo_id'] ?? 0)) ?: null;

        if (($context['es_no_aplica'] ?? 0) === 0) {
            $duplicado = (new FormatoRegistro())->duplicadoExiste(
                (int) $context['tipo_formato_id'],
                (int) $context['granja_id'],
                $galponId,
                $cicloId,
                $fechaFormato
            );

            if ($duplicado) {
                $errors[] = 'Ya existe un registro para este formato, granja, galpón/ciclo y fecha. Revise si corresponde a un duplicado.';
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function possibleSpellingWarnings(array $datos): array
    {
        $warnings = [];
        $technicalWords = [
            'bioseguridad', 'galpon', 'galpón', 'pollinaza', 'encasetamiento',
            'desinfeccion', 'desinfección', 'vacunacion', 'vacunación',
            'mortalidad', 'kriamos', 'galm', 'gaee', 'ica'
        ];

        foreach ($datos as $campo => $valor) {
            if (!is_string($valor) || trim($valor) === '') {
                continue;
            }

            $texto = mb_strtolower($valor);
            if (strlen($texto) > 20 && preg_match('/(.)\1{4,}/', $texto)) {
                $warnings[] = 'El campo ' . str_replace('_', ' ', $campo) . ' contiene repeticiones de caracteres que pueden indicar error de digitación.';
            }

            if (strlen($texto) > 30 && !preg_match('/[aeiouáéíóú]/i', $texto)) {
                $warnings[] = 'El campo ' . str_replace('_', ' ', $campo) . ' puede contener texto no legible.';
            }
        }

        return $warnings;
    }
}
