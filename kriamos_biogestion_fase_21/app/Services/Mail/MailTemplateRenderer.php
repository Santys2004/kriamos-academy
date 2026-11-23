<?php

namespace App\Services\Mail;

use App\Models\MailTemplate;

class MailTemplateRenderer
{
    public function render(string $codigo, array $data): array
    {
        $template = (new MailTemplate())->findByCodigo($codigo);

        if (!$template) {
            return [
                'asunto' => $data['asunto'] ?? 'Notificación Kriamos BioGestión ICA',
                'cuerpo_html' => $data['mensaje'] ?? '',
            ];
        }

        $subject = $template['asunto'];
        $body = $template['cuerpo_html'];

        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', (string) $value, $subject);
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return [
            'asunto' => $subject,
            'cuerpo_html' => $body,
        ];
    }
}
