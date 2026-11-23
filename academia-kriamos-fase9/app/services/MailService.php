<?php

require_once __DIR__ . '/../helpers/functions.php';

class MailService
{
    public function send(string $to, string $subject, string $body): array
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Correo destinatario inválido o vacío.'];
        }

        $config = mail_config();
        $enabled = (bool)($config['enabled'] ?? false);

        if (!$enabled) {
            $this->writePreviewLog($to, $subject, $body);
            return ['success' => true, 'message' => 'Correo simulado en ambiente local. Revise storage/logs/mail_preview.log.'];
        }

        $fromEmail = (string)($config['from_email'] ?? 'capacitaciones@kriamos.com');
        $fromName = (string)($config['from_name'] ?? 'Academia KRIAMOS');
        $replyTo = (string)($config['reply_to'] ?? $fromEmail);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . " <{$fromEmail}>",
            'Reply-To: ' . $replyTo,
            'X-Mailer: Academia KRIAMOS PHP/' . PHP_VERSION,
        ];

        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));

        return [
            'success' => $ok,
            'message' => $ok ? 'Correo enviado.' : 'No fue posible enviar el correo con mail().',
        ];
    }

    private function writePreviewLog(string $to, string $subject, string $body): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $content = "\n==============================\n";
        $content .= 'Fecha: ' . date('Y-m-d H:i:s') . "\n";
        $content .= "Para: {$to}\n";
        $content .= "Asunto: {$subject}\n";
        $content .= "Cuerpo HTML:\n{$body}\n";
        file_put_contents($logDir . '/mail_preview.log', $content, FILE_APPEND);
    }
}
