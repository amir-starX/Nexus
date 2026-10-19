<?php

declare(strict_types=1);

namespace Nexus\Bootstrap;

use Nexus\ErrorPanel\PanelFactory;
use Nexus\Http\Response;
use Nexus\Http\ResponseEmitter;
use Nexus\Kernel\Dispatcher;
use Nexus\Kernel\KernelContext;
use Nexus\Support\Env;

final class Application
{
    private function __construct(
        private readonly string $basePath,
        private readonly KernelContext $context,
    ) {
    }

    public static function create(string $basePath): self
    {
        if (!defined('NEXUS_BASE_PATH')) {
            define('NEXUS_BASE_PATH', $basePath);
        }

        Env::load($basePath . '/.env');

        $configPath = (string) Env::get('CONFIG_PATH', 'config/main.json');
        $config = ConfigLoader::load($basePath . '/' . $configPath);

        $context = ContextFactory::build($basePath, $config);

        return new self($basePath, $context);
    }

    public function handle(string $uri, string $method): ResponseEmitter
    {
        $dispatcher = new Dispatcher($this->context->dispatcher);

        try {
            $response = $dispatcher->dispatch($uri, $method, $this->context);
        } catch (\Throwable $e) {
            if ($this->context->errorPanel !== null) {
                $this->context->errorPanel->record($e, [
                    'uri' => $uri,
                    'method' => $method,
                ]);
            }
            $response = $this->renderException($e);
        }

        return new ResponseEmitter($response);
    }

    public function context(): KernelContext
    {
        return $this->context;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    private function renderException(\Throwable $e): Response
    {
        $panel = PanelFactory::fromContext($this->context);

        if ($panel->isEnabled()) {
            return Response::html($panel->render($e), 500);
        }

        return Response::text('Internal Server Error', 500);
    }
}