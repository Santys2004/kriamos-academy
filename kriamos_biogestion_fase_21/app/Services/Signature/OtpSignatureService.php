<?php

namespace App\Services\Signature;

use App\Models\OtpFirma;

class OtpSignatureService
{
    public function generate(int $usuarioId): string
    {
        $code = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 600);

        (new OtpFirma())->create($usuarioId, hash('sha256', $code), $expiresAt);

        // En producción este código debe enviarse por correo/SMS seguro.
        $_SESSION['last_signature_otp_demo'] = $code;

        return $code;
    }

    public function validate(int $usuarioId, string $code): bool
    {
        return (new OtpFirma())->validate($usuarioId, $code);
    }
}
