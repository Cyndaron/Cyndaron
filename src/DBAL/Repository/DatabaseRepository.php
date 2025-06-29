<?php
declare(strict_types=1);

namespace Cyndaron\DBAL\Repository;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\DBAL\ImproperSubclassing;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\ValueConverter;
use DateTime;
use PDOException;
use function array_fill;
use function array_key_exists;
use function count;
use function get_class;
use function implode;
use function reset;

final class DatabaseRepository implements GenericRepository
{
    use GenericRepositoryTrait;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     * @return T|null
     */
    public function fetchById(string $class, int $id): Model|null
    {
        $object = new $class($id);
        if ($this->loadModel($object))
        {
            return $object;
        }

        return null;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T[]
     */
    public function fetchAll(string $class, array $where = [], array $args = [], string $afterWhere = ''): array
    {
        $whereString = '';
        if (count($where) > 0)
        {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }
        $results = $this->connection->doQueryAndFetchAll('SELECT * FROM ' . $class::TABLE . ' ' . $whereString . ' ' . $afterWhere, $args) ?: [];
        return $this->DBResultsToModels($class, $results);
    }

    public function delete(Model $model): void
    {
        if (!$model->id)
        {
            throw new DatabaseError('No ID!');
        }

        $table = $model::TABLE;
        /** @noinspection SqlResolve */
        $this->connection->executeQuery("DELETE FROM {$table} WHERE id = ?", [$model->id]);
    }

    /**
     * @param Model $model
     * @throws PDOException
     * @return void
     */
    public function save(Model $model): void
    {
        if (empty($model::TABLE))
        {
            throw new ImproperSubclassing('TABLE not properly set!');
        }

        $tableFields = $model->getTableFields();
        if (empty($tableFields))
        {
            throw new ImproperSubclassing('Table fields not properly set!');
        }

        // Create new
        if ($model->id === null)
        {
            $arguments = [];
            $placeholders = implode(',', array_fill(0, count($tableFields), '?'));
            $quotedTableFields = [];
            foreach ($tableFields as $tableField)
            {
                $arguments[] = ValueConverter::phpToSql($model->{$tableField->propertyName});
                $quotedTableFields[] = "`{$tableField->dbName}`";
            }

            $result = $this->connection->insert('INSERT INTO ' . $model::TABLE . ' (' . implode(',', $quotedTableFields) . ') VALUES (' . $placeholders . ')', $arguments);
            if ($result !== false)
            {
                $model->id = (int)$result;
                $model->created = new DateTime();
                $model->modified = new DateTime();
            }
        }
        // Modify existing entry
        else
        {
            $setStrings = [];
            $arguments = [];

            foreach ($tableFields as $tableField)
            {
                $mangledField = ValueConverter::phpToSql($model->{$tableField->propertyName});
                if ($mangledField !== null)
                {
                    $setStrings[] = "`{$tableField->dbName}`=?";
                    $arguments[] = $mangledField;
                }
                else
                {
                    $setStrings[] = "`{$tableField->dbName}`= NULL";
                }
            }

            $arguments[] = $model->id;
            $result = $this->connection->insert('UPDATE ' . $model::TABLE . ' SET ' . implode(',', $setStrings) . ' WHERE id=?', $arguments);
            if ($result !== false)
            {
                $model->modified = new DateTime();
            }
        }
    }

    private function loadModel(Model $model): bool
    {
        if ($model->id === null)
        {
            throw new DatabaseError('ID is not set!');
        }

        $table = $model::TABLE;
        if ($table === '')
        {
            throw new DatabaseError('Model ' . get_class($model) . ' has no table name set!');
        }
        /** @noinspection SqlResolve */
        $record = $this->connection->doQueryAndFetchFirstRow("SELECT * FROM {$table} WHERE id = ?", [$model->id]);
        if ($record === null)
        {
            return false;
        }
        foreach ($model->getTableFields() as $tableField)
        {
            $model->{$tableField->propertyName} = ValueConverter::sqlToPhp($this, $record[$tableField->dbName], $model::class, $tableField->propertyName);
        }
        return true;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param list<array<string, float|int|string|null>> $results
     * @return T[]
     */
    private function DBResultsToModels(string $class, array $results): array
    {
        $ret = [];
        foreach ($results as $result)
        {
            $ret[$result['id']] = $this->DBResultToModel($class, $result);
        }

        return $ret;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param array<string, float|int|string|null> $result
     * @return T
     */
    private function DBResultToModel(string $class, array $result): Model
    {
        $model = new $class((int)$result['id']);
        $this->updateFromArray($model, $result);
        return $model;
    }
}
