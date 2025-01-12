<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

/**
 * Most Cyndaron code still uses static references to the database connection.
 * In the long run, this should be refactored, but until that time, this vestigial class has been left
 * in order to ease the transition.
 */
final class DBConnection
{
    private static Connection $pdo;

    public static function connect(Connection $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * @deprecated
     */
    public static function getPDO(): Connection
    {
        return self::$pdo;
    }
}
