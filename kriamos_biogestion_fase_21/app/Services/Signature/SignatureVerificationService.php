<?php

namespace App\Services\Signature;

use App\Models\FirmaAvanzada;
use App\Models\SelloTiempo;

class SignatureVerificationService
{
    public function verify(int $firmaId): array
    {
        $firma = (new FirmaAvanzada())->find($firmaId);

        if (!$firma) {
            return [
                'ok' => false,
                'estado' => 'no_encontrada',
                'message' => 'Firma no encontrada.',
            ];
        }

        $hashService = new DocumentHashService();

        if ($firma['tipo_objeto'] === 'formato') {
            $current = $hashService->hashFormato((int) $firma['objeto_id']);
        } elseif ($firma['tipo_objeto'] === 'documento') {
            $current = $hashService->hashDocumento((int) $firma['objeto_id']);
        } else {
            $current = [
                'ok' => false,
                'message' => 'Tipo de objeto no soportado.',
            ];
        }

        if (!$current['ok']) {
            (new FirmaAvanzada())->actualizarVerificacion($firmaId, 'error', $current['message']);
            return [
                'ok' => false,
                'estado' => 'error',
                'message' => $current['message'],
            ];
        }

        if (!hash_equals($firma['hash_original'], $current['hash'])) {
            (new FirmaAvanzada())->actualizarVerificacion($firmaId, 'alterada', 'El hash actual no coincide con el hash firmado.');
            return [
                'ok' => false,
                'estado' => 'alterada',
                'message' => 'El documento o formato fue modificado después de la firma.',
            ];
        }

        $sello = (new SelloTiempo())->byFirma($firmaId);
        if (!$sello) {
            (new FirmaAvanzada())->actualizarVerificacion($firmaId, 'sin_sello', 'Firma sin sello de tiempo.');
            return [
                'ok' => false,
                'estado' => 'sin_sello',
                'message' => 'La firma existe, pero no tiene sello de tiempo asociado.',
            ];
        }

        (new FirmaAvanzada())->actualizarVerificacion($firmaId, 'valida', 'Integridad verificada correctamente.');

        return [
            'ok' => true,
            'estado' => 'valida',
            'message' => 'Firma íntegra y sello de tiempo encontrado.',
            'firma' => $firma,
            'sello' => $sello,
        ];
    }
}
