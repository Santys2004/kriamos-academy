<?php

namespace App\Services\AI;

class DataSanitizer
{
    public function sanitize(string $text): string
    {
        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[CORREO_OCULTO]', $text);
        $text = preg_replace('/\b\d{7,10}\b/', '[NUMERO_OCULTO]', $text);
        $text = preg_replace('/\b\d{1,3}(\.\d{3}){1,3}\b/', '[NUMERO_OCULTO]', $text);
        $text = preg_replace('/\b(contraseña|password|clave)\s*[:=]\s*\S+/iu', '$1: [OCULTO]', $text);

        return $text;
    }

    public function hasSensitiveRisk(string $text): bool
    {
        return preg_match('/contraseña|password|clave|token|secreto|api key|cedula|cédula/iu', $text) === 1;
    }
}
