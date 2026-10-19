<?php

declare(strict_types=1);

namespace Nexus\Support;

final class Env
{
    public static function load(string $path): void
    {
        if (is_file($path)) {
            \Dotenv\Dotenv::createImmutable(dirname($path), basename($path))->safeLoad();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}