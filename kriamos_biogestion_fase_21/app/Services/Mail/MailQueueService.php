<?php

namespace App\Services\Mail;

use App\Models\MailQueue;
use App\Services\Database;

class MailQueueService
{
    public function enqueue(array $data): int
    {
        return (new MailQueue())->create([
            'usuario_id' => $data['usuario_id'] ?? null,
            'alerta_id' => $data['alerta_id'] ?? null,
            'notificacion_id' => $data['notificacion_id'] ?? null,
            'destinatario' => $data['destinatario'],
            'asunto' => $data['asunto'],
            'cuerpo_html' => $data['cuerpo_html'],
            'prioridad' => $data['prioridad'] ?? 'media',
            'estado' => 'pendiente',
            'intentos' => 0,
            'programado_at' => $data['programado_at'] ?? null,
        ]);
    }

    public function process(int $limit = 50): array
    {
        $queue = new MailQueue();
        $pending = $queue->pendientes($limit);
        $mailer = new SmtpMailer($this->smtpConfig());

        $result = [
            'procesados' => 0,
            'enviados' => 0,
            'errores' => 0,
        ];

        foreach ($pending as $mail) {
            $result['procesados']++;
            $send = $mailer->send($mail['destinatario'], $mail['asunto'], $mail['cuerpo_html']);

            if ($send['ok']) {
                $queue->marcarEnviado((int) $mail['id'], $send['message']);
                $result['enviados']++;
            } else {
                $queue->marcarError((int) $mail['id'], $send['message']);
                $result['errores']++;
            }
        }

        return $result;
    }

    public function smtpConfig(): array
    {
        $db = Database::connection();
        $stmt = $db->query("
            SELECT clave, valor
            FROM parametros_sistema
            WHERE grupo = 'smtp'
        ");

        $params = [];
        foreach ($stmt->fetchAll() as $row) {
            $params[$row['clave']] = $row['valor'];
        }

        return [
            'host' => $params['smtp_host'] ?? '',
            'port' => $params['smtp_port'] ?? 587,
            'username' => $params['smtp_username'] ?? '',
            'password' => $params['smtp_password'] ?? '',
            'from_address' => $params['smtp_from_address'] ?? '',
            'from_name' => $params['smtp_from_name'] ?? 'Kriamos BioGestión ICA',
        ];
    }
}
