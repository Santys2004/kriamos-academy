<?php

namespace App\Models;

class OtpFirma extends BaseModel
{
    public function create(int $usuarioId, string $codeHash, string $expiresAt): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO otp_firma (
                usuario_id, codigo_hash, estado, expires_at, created_at
            ) VALUES (
                :usuario_id, :codigo_hash, 'pendiente', :expires_at, NOW()
            )
        ");
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo_hash' => $codeHash,
            'expires_at' => $expiresAt,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function validate(int $usuarioId, string $code): bool
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM otp_firma
            WHERE usuario_id = :usuario_id
              AND estado = 'pendiente'
              AND expires_at >= NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['usuario_id' => $usuarioId]);
        $otp = $stmt->fetch();

        if (!$otp) {
            return false;
        }

        if (!hash_equals($otp['codigo_hash'], hash('sha256', $code))) {
            return false;
        }

        $update = $this->db->prepare("
            UPDATE otp_firma
            SET estado = 'usado',
                usado_at = NOW()
            WHERE id = :id
        ");
        $update->execute(['id' => $otp['id']]);

        return true;
    }
}
