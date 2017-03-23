<?php
namespace Cyndaron;

use PDO;
use PDOException;

ini_set('memory_limit', '96M');

/**
 * Zorgt voor verbinding met de database.
 */
class DBConnection
{
    private static $instance;
    private static $pdo;

    private function __construct()
    {
        static::connect();
    }

    public static function getInstance(): DBConnection
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function doQuery(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $result = $prep->execute($vars);
        return $result == false ? $result : static::$pdo->lastInsertId();
    }

    public function doQueryAndFetchAll(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetchAll();
    }

    public function doQueryAndFetchFirstRow(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetch();
    }

    public function doQueryAndFetchOne(string $query, array $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $prep->execute($vars);
        return $prep->fetchColumn();
    }

    public function errorInfo()
    {
        return static::$pdo->errorCode();
    }

    private static function connect()
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

    /**
     * @deprecated
     * @param $query
     * @param array $vars
     * @return string
     */
    public static function geefEen($query, $vars = [])
    {
        static::connect();
        $resultaat = static::$pdo->prepare($query);
        $resultaat->execute($vars);
        return $resultaat->fetchColumn();
    }

    /**
     * @deprecated
     * @param $query
     * @param $vars
     * @return string
     */
    public static function maakEen($query, $vars)
    {
        static::connect();
        $resultaat = static::$pdo->prepare($query);
        $resultaat->execute($vars);
        return static::$pdo->lastInsertId();
    }
}
