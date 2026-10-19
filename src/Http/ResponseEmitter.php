<?php

declare(strict_types=1);

namespace Nexus\Http;

final class ResponseEmitter
{
    public function __construct(private readonly Response $response)
    {
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->response->status);
            foreach ($this->response->headers as $name => $value) {
                header("{$name}: {$value}", true);
            }
        }
        echo $this->response->body;
    }
}