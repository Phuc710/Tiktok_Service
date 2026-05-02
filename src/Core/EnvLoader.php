<?php

namespace App\Core;

/**
 * EnvLoader — đọc file .env và load vào PHP environment
 */
class EnvLoader {
    private static bool $loaded = false;

    public static function load(string $envPath): void {
        if (self::$loaded) return;

        if (!file_exists($envPath)) {
            throw new \RuntimeException('.env file not found at: ' . $envPath);
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            if (!array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
