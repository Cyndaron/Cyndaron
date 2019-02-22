<?php
namespace Cyndaron;

use PDO;
use PDOException;
/**
 * Zorgt voor verbinding met de database.
 */
class DBConnection
{
    /** @var PDO $pdo */
    private static $pdo;

    public static function doQuery(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $result = $prep->execute($vars);
        return $result == false ? $result : static::$pdo->lastInsertId();
    }

    public static function doQueryAndFetchAll(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetchAll();
    }

    public static function doQueryAndFetchFirstRow(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetch();
    }

    public static function doQueryAndFetchOne(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetchColumn();
    }

    public static function errorInfo()
    {
        return static::$pdo->errorCode();
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
