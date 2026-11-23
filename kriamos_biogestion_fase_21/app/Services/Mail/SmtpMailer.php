<?php

namespace App\Services\Mail;

class SmtpMailer
{
    private array $config;
    private $socket;
    private array $log = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $html): array
    {
        try {
            $host = $this->config['host'] ?? '';
            $port = (int) ($this->config['port'] ?? 587);
            $username = $this->config['username'] ?? '';
            $password = $this->config['password'] ?? '';
            $from = $this->config['from_address'] ?? $username;
            $fromName = $this->config['from_name'] ?? 'Kriamos BioGestión ICA';

            if ($host === '' || $username === '' || $password === '' || $from === '') {
                return ['ok' => false, 'message' => 'Configuración SMTP incompleta.'];
            }

            $remote = ($port === 465 ? 'ssl://' : '') . $host;
            $this->socket = fsockopen($remote, $port, $errno, $errstr, 20);

            if (!$this->socket) {
                return ['ok' => false, 'message' => "No fue posible conectar SMTP: $errstr ($errno)"];
            }

            stream_set_timeout($this->socket, 20);
            $this->expect([220]);

            $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $this->command("EHLO $serverName", [250]);

            if ($port === 587) {
                $this->command("STARTTLS", [220]);
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->command("EHLO $serverName", [250]);
            }

            $this->command("AUTH LOGIN", [334]);
            $this->command(base64_encode($username), [334]);
            $this->command(base64_encode($password), [235]);

            $this->command("MAIL FROM:<$from>", [250]);
            $this->command("RCPT TO:<$to>", [250, 251]);
            $this->command("DATA", [354]);

            $headers = [];
            $headers[] = 'From: ' . $this->encodeHeader($fromName) . " <$from>";
            $headers[] = "To: <$to>";
            $headers[] = 'Subject: ' . $this->encodeHeader($subject);
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';
            $headers[] = 'Date: ' . date('r');

            $message = implode("\r\n", $headers) . "\r\n\r\n" . $html . "\r\n.";
            $this->command($message, [250]);
            $this->command("QUIT", [221]);
            fclose($this->socket);

            return ['ok' => true, 'message' => implode("\n", $this->log)];
        } catch (\Throwable $e) {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            return ['ok' => false, 'message' => $e->getMessage() . "\n" . implode("\n", $this->log)];
        }
    }

    private function command(string $command, array $expectedCodes): string
    {
        fwrite($this->socket, $command . "\r\n");
        return $this->expect($expectedCodes);
    }

    private function expect(array $expectedCodes): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $this->log[] = trim($response);
        $code = (int) substr($response, 0, 3);

        if (!in_array($code, $expectedCodes, true)) {
            throw new \RuntimeException('Respuesta SMTP inesperada: ' . trim($response));
        }

        return $response;
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
