<?php

namespace App\Middleware\Api;

use App\Models\ApiLog;
use App\Models\ApiToken;
use App\Services\Api\ApiResponse;

class ApiAuthMiddleware
{
    private static ?array $token = null;
    private static float $startedAt = 0;

    public static function handle(): array
    {
        self::$startedAt = microtime(true);

        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\\s+(.+)/', $authorization, $matches)) {
            ApiResponse::error('Token Bearer requerido.', 401);
        }

        $plainToken = trim($matches[1]);
        $token = (new ApiToken())->findValidByPlainToken($plainToken);

        if (!$token) {
            ApiResponse::error('Token inválido, vencido o revocado.', 401);
        }

        self::validateIp($token);
        self::rateLimit($token);

        (new ApiToken())->touchLastUsed((int) $token['id']);
        self::$token = $token;

        return $token;
    }

    public static function currentToken(): ?array
    {
        return self::$token;
    }

    public static function log(int $statusCode): void
    {
        if (!self::$token) {
            return;
        }

        $responseMs = (int) round((microtime(true) - self::$startedAt) * 1000);

        (new ApiLog())->create([
            'api_client_id' => self::$token['api_client_id'],
            'api_token_id' => self::$token['id'],
            'metodo' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '',
            'status_code' => $statusCode,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'request_hash' => hash('sha256', ($_SERVER['REQUEST_METHOD'] ?? '') . '|' . ($_SERVER['REQUEST_URI'] ?? '') . '|' . file_get_contents('php://input')),
            'response_ms' => $responseMs,
        ]);
    }

    private static function validateIp(array $token): void
    {
        $allowed = trim((string) ($token['ip_permitidas'] ?? ''));

        if ($allowed === '') {
            return;
        }

        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $ips = array_filter(array_map('trim', explode(',', $allowed)));

        if (!in_array($currentIp, $ips, true)) {
            ApiResponse::error('IP no autorizada para este cliente API.', 403);
        }
    }

    private static function rateLimit(array $token): void
    {
        $max = 2000;
        $today = (new ApiLog())->countToday((int) $token['id']);

        if ($today >= $max) {
            ApiResponse::error('Límite diario de consumo API superado.', 429, [
                'limit' => $max,
                'used_today' => $today,
            ]);
        }
    }
}
