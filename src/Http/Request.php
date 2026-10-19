<?php

declare(strict_types=1);

namespace Nexus\Http;

final class Request
{
    /** @var array<string, mixed> */
    public array $attributes = [];

    public function __construct(
        public readonly string $uri,
        public readonly string $method,
        public readonly array $headers = [],
        public readonly array $query = [],
        public readonly array $body = [],
        public readonly array $server = [],
    ) {
    }

    public static function capture(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Strip the script's directory prefix (e.g. /amir-starx/project/public)
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        return new self(
            uri: $path,
            method: strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            headers: self::headers(),
            query: $_GET,
            body: $_POST,
            server: $_SERVER,
        );
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    private static function headers(): array
    {
        $out = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $out[$name] = (string) $value;
            }
        }
        return $out;
    }
}