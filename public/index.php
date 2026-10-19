<?php

declare(strict_types=1);

use Nexus\Bootstrap\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

Application::create(dirname(__DIR__))
    ->handle($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET')
    ->send();