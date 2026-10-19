<?php

declare(strict_types=1);

namespace Nexus\Support;

final class ComposerInstaller
{
    /** @var array<string, bool> */
    private static array $attempted = [];

    public static function install(string $basePath, string $class): void
    {
        if (isset(self::$attempted[$class])) {
            return;
        }
        self::$attempted[$class] = true;

        $package = self::guessPackage($class);
        if ($package === null) {
            return;
        }

        $composerJson = $basePath . '/composer.json';
        if (!is_file($composerJson)) {
            return;
        }

        $cmd = sprintf('cd %s && composer require --no-interaction %s 2>&1', escapeshellarg($basePath), escapeshellarg($package));
        @exec($cmd, $output, $code);

        if ($code === 0) {
            $autoload = $basePath . '/vendor/autoload.php';
            if (is_file($autoload)) {
                require_once $autoload;
            }
        }
    }

    public static function guessPackage(string $class): ?string
    {
        $map = [
            'Illuminate\\' => 'illuminate/container',
            'Symfony\\Component\\' => 'symfony/' . strtolower(explode('\\', $class)[2] ?? 'console'),
            'Slim\\' => 'slim/slim',
            'Laminas\\' => 'laminas/laminas-servicemanager',
            'Doctrine\\' => 'doctrine/orm',
            'Twig\\' => 'twig/twig',
            'Latte\\' => 'latte/latte',
            'Monolog\\' => 'monolog/monolog',
            'FastRoute\\' => 'nikic/fast-route',
            'League\\' => 'league/container',
        ];

        foreach ($map as $prefix => $package) {
            if (str_starts_with($class, $prefix)) {
                return $package;
            }
        }
        return null;
    }
}