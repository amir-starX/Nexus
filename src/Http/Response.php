<?php

declare(strict_types=1);

namespace Nexus\Http;

final class Response
{
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly string $body,
    ) {
    }

    public static function html(string $body, int $status = 200, array $headers = []): self
    {
        return new self($status, $headers + ['Content-Type' => 'text/html; charset=utf-8'], $body);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($status, $headers + ['Content-Type' => 'application/json; charset=utf-8'], $body);
    }

    public static function text(string $body, int $status = 200, array $headers = []): self
    {
        return new self($status, $headers + ['Content-Type' => 'text/plain; charset=utf-8'], $body);
    }

    public static function notFound(string $body = 'Not Found'): self
    {
        return self::text($body, 404);
    }

    public function withHeader(string $name, string $value): self
    {
        return new self($this->status, $this->headers + [$name => $value], $this->body);
    }
}