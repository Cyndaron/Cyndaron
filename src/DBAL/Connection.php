<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use PDO;
use PDOException;
use PDOStatement;
use function assert;
use function is_array;
use function is_scalar;
use function Safe\error_log;

final class Connection extends PDO
{
    /**
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array<string, string|int>|null $options
     */
    public function __construct(string $dsn, string|null $username = null, string|null $password = null, array|null $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Setting this to false makes PDO use native prepared statements.
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function create(string $engine, string $host, string $databaseName, string $user, string $password): self
    {
        try
        {
            $pdo = @new self($engine . ':host=' . $host . ';dbname=' . $databaseName . ';charset=utf8mb4', $user, $password);
        }
        catch (PDOException $e)
        {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log((string)$e);
            throw new DatabaseError('Kan niet verbinden met database!');
        }

        return $pdo;
    }

    /**
     * @param string $query
     * @param array<int|string, string|int|float|null> $vars
     * @throws PDOException
     * @return PDOStatement
     */
    public function executeQuery(string $query, array $vars = []): PDOStatement
    {
        $prep = $this->prepare($query);
        $result = $prep->execute($vars);
        assert($result !== false);
        return $prep;
    }

    /**
     * @param string $query
     * @phpstan-ignore-next-line
     * @param array<string, string|int> $options
     * @return PDOStatement
     */
    public function prepare(string $query, array $options = []): PDOStatement
    {
        $result = parent::prepare($query, $options);
        assert($result !== false);
        return $result;
    }

    /**
     * @param string $query
     * @param array<int|string, string|int|float|null> $vars
     * @return int|false
     */
    public function insert(string $query, array $vars = []): int|false
    {
        $this->executeQuery($query, $vars);
        $lastId = $this->lastInsertId();
        return ($lastId !== false) ? (int)$lastId : false;
    }

    /**
     * @param string $query
     * @param array<int|string, string|int|float|null> $vars
     * @return list<array<string, float|int|string|null>>
     */
    public function doQueryAndFetchAll(string $query, array $vars = []): array
    {
        $stmt = $this->executeQuery($query, $vars);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @param string $query
     * @param array<int|string, string|int|float|null> $vars
     * @return array<string, float|int|string|null>|null Returns null if there are no matching rows.
     */
    public function doQueryAndFetchFirstRow(string $query, array $vars = []): array|null
    {
        $stmt = $this->executeQuery($query, $vars);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
        {
            return null;
        }
        assert(is_array($result));
        return $result;
    }

    /**
     * @param string $query
     * @param array<int|string, string|int|float|null> $vars
     * @return string|null Returns null if there are no matching rows.
     */
    public function doQueryAndFetchOne(string $query, array $vars = []): string|null
    {
        $stmt = $this->executeQuery($query, $vars);
        $result = $stmt->fetchColumn();
        if ($result === false)
        {
            return null;
        }
        assert(is_scalar($result));
        return (string)$result;
    }
}
