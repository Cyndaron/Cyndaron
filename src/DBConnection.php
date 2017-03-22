<?php
namespace Cyndaron;

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

    public static function getInstance()
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function doQuery($query, $vars = [])
    {
        $prep = static::$pdo->prepare($query);
        $result = $prep->execute($vars);
        return $result == FALSE ? $result : static::$pdo->lastInsertId();
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
        $dbmethode = 'mysql';
        $dbuser = 'root';
        $dbpass = '';
        $dbplek = 'localhost';
        $dbnaam = 'cyndaron';
        require __DIR__ . '/../instellingen.php';

        try
        {
            static::$pdo = @new \PDO($dbmethode . ':host=' . $dbplek . ';dbname=' . $dbnaam . ';charset=utf8', $dbuser, $dbpass);
        }
        catch(\PDOException $e)
        {
            error_log($e);
            echo 'Kan niet verbinden met database!<br>';
            echo 'Foutmelding: ' . $e->getMessage();
            die();
        }
    }

    public static function getPdo()
    {
        if (static::$pdo === null)
        {
            static::connect();
        }
    }
}
