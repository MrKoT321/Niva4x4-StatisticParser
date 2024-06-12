<?php
declare(strict_types=1);

namespace App\Database;

readonly class UserTable
{
    public function __construct(
        private \PDO $connection
    ) {
    }

    public function saveUsers(array $users): void
    {
    }
}