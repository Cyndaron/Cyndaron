<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

/**
 * Zorgt voor verbinding met de database.
 */
final class DBConnection
{
    private static Connection $pdo;

    public static function connect(Connection $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function getPDO(): Connection
    {
        return self::$pdo;
    }
}
