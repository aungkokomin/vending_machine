<?php

declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    static $loaded = false;

    if (!$loaded) {
        $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = array_map('trim', explode('=', $line, 2));
                $_ENV[$name] = $value;
            }
        }
        $loaded = true;
    }

    return $_ENV[$key] ?? getenv($key) ?: $default;
}

return [
    'db' => [
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => env_value('DB_PORT', '3306'),
        'name' => env_value('DB_NAME', 'vending_machine'),
        'user' => env_value('DB_USER', 'root'),
        'pass' => env_value('DB_PASS', ''),
    ],
    'jwt_secret' => env_value('JWT_SECRET', 'change-this-secret'),
];
