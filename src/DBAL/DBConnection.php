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
        $prep = static::$pdo->prepare($query);
        $result = $prep->execute($vars);
        if (!$result)
        {
            static::$statementError = $prep->errorInfo();
            static::$errorQuery = $query;
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
        $result = static::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return static::$pdo->lastInsertId();
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
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
        {
            return $prep->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    public static function doQueryAndReturnFetchable(string $query, array $vars = []): PDOStatement
    {
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
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
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result)
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
        return static::executeQuery($query, $vars, static function(PDOStatement$prep, $result)
        {
            return $prep->fetchColumn();
        });
    }

    public static function errorInfo(): array
    {
        return ['pdo' => static::$pdo->errorInfo(), 'statement' => static::$statementError, 'query' => static::$errorQuery];
    }

    public static function connect(string $engine, string $host, string $databaseName, string $user, string $password): void
    {
        try
        {
            static::$pdo = @new PDO($engine . ':host=' . $host . ';dbname=' . $databaseName . ';charset=utf8mb4', $user, $password);
            static::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Setting this to false makes PDO use native prepared statements.
            static::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
        return static::$pdo;
    }
}
