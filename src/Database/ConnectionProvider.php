<?php
declare(strict_types=1);

namespace App\Database;

use App\Environment;

final class ConnectionProvider
{
    private const DATABASE_CONFIG_NAME = 'db.ini';
    private const DSN_KEY = 'dsn';
    private const USER_KEY = 'user';
    private const PASSWORD_KEY = 'password';
    private const EXPECTED_KEYS = [self::DSN_KEY, self::USER_KEY, self::PASSWORD_KEY];

    public static function connectDatabase(): \PDO
    {
        $configPath = Environment::getConfigPath(self::DATABASE_CONFIG_NAME);
        if (!file_exists($configPath))
        {
            throw new \RuntimeException("Could not find database configuration at '$configPath'");
        }

        $config = parse_ini_file($configPath);
        if (!$config)
        {
            throw new \RuntimeException("Failed to parse database configuration from '$configPath'");
        }

        $missingKeys = array_diff(self::EXPECTED_KEYS, array_keys($config));
        if ($missingKeys)
        {
            throw new \RuntimeException(
                'Wrong database configuration: missing options ' . implode(' ', $missingKeys)
            );
        }

        return new \PDO($config[self::DSN_KEY], $config[self::USER_KEY], $config[self::PASSWORD_KEY]);
    }
}