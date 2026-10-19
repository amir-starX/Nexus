<?php

declare(strict_types=1);

namespace Nexus\Config;

final class Config
{
    public function __construct(
        private readonly array $data,
        private readonly string $basePath,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $cursor = $this->data;

        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    public function has(string $key): bool
    {
        return $this->get($key, self::sentinel()) !== self::sentinel();
    }

    public function all(): array
    {
        return $this->data;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function isDebug(): bool
    {
        return (bool) $this->get('app.debug', false);
    }

    private static function sentinel(): object
    {
        static $sentinel = null;
        return $sentinel ??= new \stdClass();
    }
}