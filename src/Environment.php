<?php
declare(strict_types=1);

namespace App;

final readonly class Environment
{
    private const HTTP_STATUS_303_SEE_OTHER = 303;
    private const CONFIG_DIR_NAME = 'config';
    private const PUBLIC_DIR_NAME = 'public';
    private const STATISTIC_DIR_NAME = 'statistic';
    private const TEMPLATES_DIR_NAME = 'templates';

    public static function joinPath(string ...$components): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($components));
    }

    public static function getConfigPath(string $configFileName): string
    {
        return self::joinPath(self::getProjectRootPath(), self::CONFIG_DIR_NAME, $configFileName);
    }

    public static function getStatisticPath(string $statisticFileName): string
    {
        return self::joinPath(self::getProjectRootPath(), self::PUBLIC_DIR_NAME, self::STATISTIC_DIR_NAME, $statisticFileName);
    }

    public static function writeRedirectSeeOther(string $url): void
    {
        header('Location: ' . $url, true, self::HTTP_STATUS_303_SEE_OTHER);
    }

    private static function getProjectRootPath(): string
    {
        return dirname(__DIR__, 1);
    }
}