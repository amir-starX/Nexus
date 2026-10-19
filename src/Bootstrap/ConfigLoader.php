<?php

declare(strict_types=1);

namespace Nexus\Bootstrap;

use Nexus\Exception\ConfigException;

final class ConfigLoader
{
    public static function load(string $path): array
    {
        if (!is_file($path)) {
            throw new ConfigException("Config file not found: {$path}");
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new ConfigException("Config file unreadable: {$path}");
        }

        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new ConfigException("Config root must be an object: {$path}");
        }

        return $data;
    }
}