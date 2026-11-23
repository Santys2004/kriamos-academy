<?php

namespace App\Services\BIExternal;

use App\Models\BiExternalConnection;

class BiExternalAuthService
{
    private ?array $connection = null;

    public function authenticate(): ?array
    {
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.+)/', $authorization, $matches)) {
            return null;
        }

        $token = trim($matches[1]);
        $connection = (new BiExternalConnection())->findByToken($token);

        if (!$connection) {
            return null;
        }

        $allowedIps = trim((string) ($connection['ip_permitidas'] ?? ''));

        if ($allowedIps !== '') {
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $ips = array_filter(array_map('trim', explode(',', $allowedIps)));

            if (!in_array($currentIp, $ips, true)) {
                return null;
            }
        }

        (new BiExternalConnection())->touch((int) $connection['id']);
        $this->connection = $connection;

        return $connection;
    }

    public function connection(): ?array
    {
        return $this->connection;
    }
}
