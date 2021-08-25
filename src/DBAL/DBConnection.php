<?php
/** @noinspection PhpUnusedParameterInspection */

namespace Cyndaron\DBAL;

use PDO;
use PDOException;
use PDOStatement;

use function Safe\error_log;

/**
 * Zorgt voor verbinding met de database.
 */
final class DBConnection
{
    private static PDO $pdo;
    private static array $statementError = [];
    private static string $errorQuery = '';

    /**
     * @param string $query
     * @param array $vars
     * @param callable $functionOnSuccess
     * @return mixed
     */
    private static function executeQuery(string $query, array $vars, callable $functionOnSuccess)
    {
        $prep = self::$pdo->prepare($query);
        $result = $prep->execute($vars);
        if (!$result)
        {
            self::$statementError = $prep->errorInfo();
            self::$errorQuery = $query;
            return false;
        }

        return $functionOnSuccess($prep, $result);
    }

    /**
     * @param string $query
     * @param array $vars
     * @return int|false
     */
    public static function doQuery(string $query, array $vars = [])
    {
        $result = self::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return self::$pdo->lastInsertId();
        });
        return ($result === false) ? false : (int)$result;
    }

    /**
     * @param string $query
     * @param array $vars
     * @return array|false
     */
    public static function doQueryAndFetchAll(string $query, array $vars = [])
    {
        return self::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return $prep->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    public static function doQueryAndReturnFetchable(string $query, array $vars = []): PDOStatement
    {
        return self::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return $prep;
        });
    }

    /**
     * @param string $query
     * @param array $vars
     * @return array|false
     */
    public static function doQueryAndFetchFirstRow(string $query, array $vars = [])
    {
        return self::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return $prep->fetch(PDO::FETCH_ASSOC);
        });
    }

    /**
     * @param string $query
     * @param array $vars
     * @return mixed
     */
    public static function doQueryAndFetchOne(string $query, array $vars = [])
    {
        return self::executeQuery($query, $vars, static function(PDOStatement$prep, $result)
        {
            return $prep->fetchColumn();
        });
    }

    public static function errorInfo(): array
    {
        return ['pdo' => self::$pdo->errorInfo(), 'statement' => self::$statementError, 'query' => self::$errorQuery];
    }

    public static function connect(string $engine, string $host, string $databaseName, string $user, string $password): void
    {
        try
        {
            self::$pdo = @new PDO($engine . ':host=' . $host . ';dbname=' . $databaseName . ';charset=utf8mb4', $user, $password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Setting this to false makes PDO use native prepared statements.
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        catch (PDOException $e)
        {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e);
            throw new DatabaseError('Kan niet verbinden met database!');
        }
    }

    public static function getPDO(): PDO
    {
        return self::$pdo;
    }
}
