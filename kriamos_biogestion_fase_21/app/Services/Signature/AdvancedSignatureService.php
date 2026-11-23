<?php

namespace App\Services\Signature;

use App\Models\CertificadoFirma;
use App\Models\FirmaAvanzada;
use App\Models\SelloTiempo;
use App\Services\Auth;

class AdvancedSignatureService
{
    public function signFormato(int $formatoRegistroId, string $otpCode): array
    {
        $usuarioId = Auth::id();

        if (!(new OtpSignatureService())->validate($usuarioId, $otpCode)) {
            return [
                'ok' => false,
                'message' => 'OTP inválido o vencido.',
            ];
        }

        $cert = (new CertificadoFirma())->activeByUsuario($usuarioId);
        if (!$cert) {
            return [
                'ok' => false,
                'message' => 'El usuario no tiene certificado interno activo para firma avanzada.',
            ];
        }

        $hashService = new DocumentHashService();
        $hash = $hashService->hashFormato($formatoRegistroId);

        if (!$hash['ok']) {
            return $hash;
        }

        $evidence = [
            'usuario_id' => $usuarioId,
            'certificado_id' => $cert['id'],
            'tipo_objeto' => 'formato',
            'formato_registro_id' => $formatoRegistroId,
            'hash_original' => $hash['hash'],
            'timestamp_local' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'canonical' => $hash['canonical'],
        ];

        $signatureHash = hash('sha256', json_encode($evidence, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $firmaId = (new FirmaAvanzada())->create([
            'usuario_id' => $usuarioId,
            'certificado_id' => $cert['id'],
            'formato_registro_id' => $formatoRegistroId,
            'documento_id' => null,
            'tipo_objeto' => 'formato',
            'objeto_id' => $formatoRegistroId,
            'hash_original' => $hash['hash'],
            'hash_firma' => $signatureHash,
            'evidencia_json' => json_encode($evidence, JSON_UNESCAPED_UNICODE),
            'estado' => 'firmada',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        $selloId = $this->sealInternal($firmaId, $signatureHash);
        (new FirmaAvanzada())->marcarSellada($firmaId, $selloId);

        return [
            'ok' => true,
            'message' => 'Firma avanzada generada y sellada internamente.',
            'firma_id' => $firmaId,
            'sello_id' => $selloId,
            'hash_firma' => $signatureHash,
        ];
    }

    public function signDocumento(int $documentoId, string $otpCode): array
    {
        $usuarioId = Auth::id();

        if (!(new OtpSignatureService())->validate($usuarioId, $otpCode)) {
            return [
                'ok' => false,
                'message' => 'OTP inválido o vencido.',
            ];
        }

        $cert = (new CertificadoFirma())->activeByUsuario($usuarioId);
        if (!$cert) {
            return [
                'ok' => false,
                'message' => 'El usuario no tiene certificado interno activo para firma avanzada.',
            ];
        }

        $hashService = new DocumentHashService();
        $hash = $hashService->hashDocumento($documentoId);

        if (!$hash['ok']) {
            return $hash;
        }

        $evidence = [
            'usuario_id' => $usuarioId,
            'certificado_id' => $cert['id'],
            'tipo_objeto' => 'documento',
            'documento_id' => $documentoId,
            'hash_original' => $hash['hash'],
            'timestamp_local' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'canonical' => $hash['canonical'],
        ];

        $signatureHash = hash('sha256', json_encode($evidence, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $firmaId = (new FirmaAvanzada())->create([
            'usuario_id' => $usuarioId,
            'certificado_id' => $cert['id'],
            'formato_registro_id' => null,
            'documento_id' => $documentoId,
            'tipo_objeto' => 'documento',
            'objeto_id' => $documentoId,
            'hash_original' => $hash['hash'],
            'hash_firma' => $signatureHash,
            'evidencia_json' => json_encode($evidence, JSON_UNESCAPED_UNICODE),
            'estado' => 'firmada',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'dispositivo' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        $selloId = $this->sealInternal($firmaId, $signatureHash);
        (new FirmaAvanzada())->marcarSellada($firmaId, $selloId);

        return [
            'ok' => true,
            'message' => 'Documento firmado y sellado internamente.',
            'firma_id' => $firmaId,
            'sello_id' => $selloId,
            'hash_firma' => $signatureHash,
        ];
    }

    public function sealInternal(int $firmaId, string $signatureHash): int
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $token = hash('sha256', $firmaId . '|' . $signatureHash . '|' . $timestamp . '|KRIAMOS_INTERNAL_TSA');

        return (new SelloTiempo())->create([
            'firma_avanzada_id' => $firmaId,
            'proveedor' => 'KRIAMOS_INTERNAL_TSA',
            'timestamp_utc' => $timestamp,
            'hash_sellado' => hash('sha256', $signatureHash . '|' . $timestamp),
            'token_sello' => $token,
            'respuesta_json' => json_encode([
                'provider' => 'KRIAMOS_INTERNAL_TSA',
                'timestamp_utc' => $timestamp,
                'algorithm' => 'SHA-256',
                'token' => $token,
            ], JSON_UNESCAPED_UNICODE),
            'estado' => 'emitido',
        ]);
    }
}
