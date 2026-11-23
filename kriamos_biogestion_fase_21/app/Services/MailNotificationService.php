<?php

namespace App\Services;

class MailNotificationService
{
    public function sendAlert(array $alert): bool
    {
        if (empty($alert['email'])) {
            return false;
        }

        $config = require dirname(__DIR__, 2) . '/config/alerts.php';

        $subject = '[Kriamos BioGestión ICA] ' . $alert['titulo'];
        $message = $this->renderAlert($alert);
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from_address'] . '>',
        ];

        // Base funcional con mail() de PHP. En producción se recomienda SMTP con PHPMailer o Symfony Mailer.
        return @mail($alert['email'], $subject, $message, implode("\r\n", $headers));
    }

    public function renderAlert(array $alert): string
    {
        $titulo = htmlspecialchars($alert['titulo'] ?? 'Alerta Kriamos', ENT_QUOTES, 'UTF-8');
        $mensaje = nl2br(htmlspecialchars($alert['mensaje'] ?? '', ENT_QUOTES, 'UTF-8'));
        $prioridad = htmlspecialchars($alert['prioridad'] ?? 'media', ENT_QUOTES, 'UTF-8');

        return "
            <html>
                <body style='font-family: Arial, sans-serif; color: #1f2a3d;'>
                    <div style='border:1px solid #dbe3ef;border-radius:14px;padding:20px;'>
                        <h2 style='color:#143560;margin-top:0;'>$titulo</h2>
                        <p><strong>Prioridad:</strong> $prioridad</p>
                        <p>$mensaje</p>
                        <p style='font-size:12px;color:#68758a;'>Mensaje automático generado por Kriamos BioGestión ICA.</p>
                    </div>
                </body>
            </html>
        ";
    }
}
