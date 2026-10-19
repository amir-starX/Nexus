<?php

declare(strict_types=1);

if (!function_exists('nexus_env')) {
    function nexus_env(string $key, mixed $default = null): mixed
    {
        return \Nexus\Support\Env::get($key, $default);
    }
}

if (!function_exists('nexus_path')) {
    function nexus_path(string $path = ''): string
    {
        $base = defined('NEXUS_BASE_PATH') ? NEXUS_BASE_PATH : dirname(__DIR__, 2);
        return $path === '' ? $base : $base . '/' . ltrim($path, '/');
    }
}