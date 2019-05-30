<?php /** @noinspection PhpUnusedParameterInspection */

namespace Cyndaron;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Zorgt voor verbinding met de database.
 */
class DBConnection
{
    /** @var PDO $pdo */
    private static $pdo;
    private static $statementError = [];
    private static $errorQuery = '';

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
        else
        {
            return call_user_func($functionOnSuccess, $prep, $result);
        }
    }

    public static function doQuery(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, function(PDOStatement $prep, $result) {
            return static::$pdo->lastInsertId();
        });
    }

    public static function doQueryAndFetchAll(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, function(PDOStatement $prep, $result) {
            return $prep->fetchAll();
        });
    }

    public static function doQueryAndFetchFirstRow(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, function(PDOStatement$prep, $result) {
            return $prep->fetch();
        });
    }

    public static function doQueryAndFetchOne(string $query, array $vars = [])
    {
        return static::executeQuery($query, $vars, function(PDOStatement$prep, $result) {
            return $prep->fetchColumn();
        });
    }

    public static function errorInfo()
    {
        return ['pdo' => static::$pdo->errorInfo(), 'statement' => static::$statementError, 'query' => static::$errorQuery];
    }

    public static function connect()
    {
        if (static::$pdo !== null)
        {
            return;
        }

        $dbmethode = 'mysql';
        $dbuser = 'root';
        $dbpass = '';
        $dbplek = 'localhost';
        $dbnaam = 'cyndaron';
        require __DIR__ . '/../instellingen.php';

        try
        {
            static::$pdo = @new PDO($dbmethode . ':host=' . $dbplek . ';dbname=' . $dbnaam . ';charset=utf8', $dbuser, $dbpass);
        }
        catch(PDOException $e)
        {
            error_log($e);
            echo 'Kan niet verbinden met database!<br>';
            echo 'Foutmelding: ' . $e->getMessage();
            die();
        }
    }

    public static function getPdo(): PDO
    {
        static::connect();
        return static::$pdo;
    }
}
