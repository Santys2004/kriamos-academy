<?php

namespace App\Services\Api;

class ApiResponse
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Kriamos-Api-Version: 1.0');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $status = 400, array $extra = []): void
    {
        self::json(array_merge([
            'ok' => false,
            'message' => $message,
            'status' => $status,
        ], $extra), $status);
    }

    public static function success(array $data = [], int $status = 200): void
    {
        self::json(array_merge([
            'ok' => true,
        ], $data), $status);
    }
}
