<?php /** @noinspection PhpUnusedParameterInspection */

namespace Cyndaron;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Zorgt voor verbinding met de database.
 */
class DBConnection
{
    private static PDO $pdo;
    private static array $statementError = [];
    private static string $errorQuery = '';

    private static function executeQuery(string $query, array $vars, callable $functionOnSuccess)
    {
        $prep = static::$pdo->prepare($query);
        $result = $prep->execute($vars);
        if ($result === false)
        {
            static::$statementError = $prep->errorInfo();
            static::$errorQuery = $query;
            return false;
        }

        return call_user_func($functionOnSuccess, $prep, $result);
    }

    public static function doQuery(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result) {
            return static::$pdo->lastInsertId();
        });
    }

    public static function doQueryAndFetchAll(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result) {
            return $prep->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    public static function doQueryAndReturnFetchable(string $query, array $vars = []): PDOStatement
    {
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result) {
            return $prep;
        });
    }

    public static function doQueryAndFetchFirstRow(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, static function(PDOStatement $prep, $result) {
            return $prep->fetch(PDO::FETCH_ASSOC);
        });
    }

    public static function doQueryAndFetchOne(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, static function(PDOStatement$prep, $result) {
            return $prep->fetchColumn();
        });
    }

    public static function errorInfo(): array
    {
        return ['pdo' => static::$pdo->errorInfo(), 'statement' => static::$statementError, 'query' => static::$errorQuery];
    }

    public static function connect($dbmethode, $dbplek, $dbnaam, $dbuser, $dbpass): void
    {
        try
        {
            static::$pdo = @new PDO($dbmethode . ':host=' . $dbplek . ';dbname=' . $dbnaam . ';charset=utf8mb4', $dbuser, $dbpass);
        }
        catch(PDOException $e)
        {
            error_log($e);
            throw new RuntimeException('Kan niet verbinden met database!');
        }
    }

    public static function getPdo(): PDO
    {
        return static::$pdo;
    }
}
