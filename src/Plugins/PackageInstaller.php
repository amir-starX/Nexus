<?php

declare(strict_types=1);

namespace Nexus\Plugins;

final class PackageInstaller
{
    /** @var array<string, bool> */
    private array $installed = [];

    public function __construct(private readonly string $composerJsonPath)
    {
    }

    public function ensure(string $package): bool
    {
        if (isset($this->installed[$package])) {
            return $this->installed[$package];
        }

        if ($this->alreadyInComposerJson($package)) {
            return $this->installed[$package] = true;
        }

        $base = dirname($this->composerJsonPath);
        $cmd = sprintf(
            'cd %s && composer require --no-interaction --no-progress %s 2>&1',
            escapeshellarg($base),
            escapeshellarg($package),
        );

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);

        $ok = $code === 0;
        if ($ok) {
            $autoload = $base . '/vendor/autoload.php';
            if (is_file($autoload)) {
                require_once $autoload;
            }
        }

        return $this->installed[$package] = $ok;
    }

    private function alreadyInComposerJson(string $package): bool
    {
        if (!is_file($this->composerJsonPath)) {
            return false;
        }

        $json = json_decode((string) file_get_contents($this->composerJsonPath), true);
        if (!is_array($json)) {
            return false;
        }

        return isset($json['require'][$package])
            || isset($json['require-dev'][$package]);
    }
}