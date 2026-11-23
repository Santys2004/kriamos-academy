<?php

function env_value(string $key, mixed $default = null): mixed
{
    static $env = null;

    if ($env === null) {
        $env = [];
        $path = dirname(__DIR__) . '/.env';

        if (file_exists($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $env[trim($name)] = $value;
            }
        }
    }

    return $env[$key] ?? $default;
}
