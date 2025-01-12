<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use DateTime;
use Exception;

use function array_map;
use function sprintf;
use function array_merge;
use function reset;
use function array_key_exists;
use function array_fill;
use function count;
use function implode;
use function in_array;
use function array_keys;

abstract class Model
{
    public const TABLE = '';

    #[DatabaseField]
    public int|null $id;
    #[DatabaseField]
    public DateTime $modified;
    #[DatabaseField]
    public DateTime $created;

    final public function __construct(int|null $id = null)
    {
        $this->id = $id;
        $this->created = new DateTime();
        $this->modified = new DateTime();
    }

    /**
     * @param int $id
     * @throws Exception
     * @return static|null
     */
    public static function fetchById(int $id): Model|null
    {
        // Needed to make sure an object of the derived class is returned, not one of the base class.
        $object = new static($id);
        if ($object->load())
        {
            return $object;
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getTableFields(bool $extended = false): array
    {
        $ret = [];
        $reflectionClass = new \ReflectionClass($this);
        foreach ($reflectionClass->getProperties() as $property)
        {
            $name = $property->getName();
            if ($name === 'id')
            {
                continue;
            }
            if (!$extended && in_array($name, ['created', 'modified'], true))
            {
                continue;
            }
            if (count($property->getAttributes(DatabaseField::class)) > 0)
            {
                $ret[] = $name;
            }
        }
        return $ret;
    }

    public function load(): bool
    {
        if ($this->id === null)
        {
            throw new DatabaseError('ID is not set!');
        }

        $table = static::TABLE;
        /** @noinspection SqlResolve */
        $record = DBConnection::getPDO()->doQueryAndFetchFirstRow("SELECT * FROM {$table} WHERE id = ?", [$this->id]);
        if ($record === null)
        {
            return false;
        }
        foreach (array_keys($record) as $tableField)
        {
            $this->$tableField = ValueConverter::sqlToPhp($record[$tableField], static::class, $tableField);
        }
        return true;
    }

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return static[]
     */
    public static function fetchAll(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        $whereString = '';
        if (count($where) > 0)
        {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }
        $results = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM ' . static::TABLE . ' ' . $whereString . ' ' . $afterWhere, $args) ?: [];
        return self::DBResultsToModels($results);
    }

    /**
     * @param list<array<string, float|int|string|null>> $results
     * @return static[]
     */
    protected static function DBResultsToModels(array $results): array
    {
        $ret = [];
        foreach ($results as $result)
        {
            $ret[$result['id']] = static::DBResultToModel($result);
        }

        return $ret;
    }

    /**
     * @param array<string, float|int|string|null> $result
     * @return static
     */
    protected static function DBResultToModel(array $result): self
    {
        $obj = new static((int)$result['id']);
        $obj->updateFromArray($result);
        return $obj;
    }

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return static|null
     */
    public static function fetch(array $where = [], array $args = [], string $afterWhere = ''): self|null
    {
        $results = static::fetchAll($where, $args, $afterWhere);
        if (count($results) > 0)
        {
            $firstElem = reset($results);
            return $firstElem;
        }

        return null;
    }

    /**
     * @return array<string, float|int|string|null>
     */
    public function asArray(): array
    {
        $return = [];
        foreach ($this->getTableFields(true) as $tableField)
        {
            $return[$tableField] = $this->$tableField;
        }
        return $return;
    }

    /**
     * @param array<string, float|int|string|null> $array
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $sub = new static((int)$array['id']);
        $sub->updateFromArray($array);
        return $sub;
    }

    /**
     * @param array<string, float|int|string|null> $newArray
     * @return bool
     */
    public function updateFromArray(array $newArray): bool
    {
        $couldUpdateAll = true;
        foreach ($this->getTableFields(true) as $tableField)
        {
            if (array_key_exists($tableField, $newArray))
            {
                $this->$tableField = ValueConverter::sqlToPhp($newArray[$tableField], static::class, $tableField);
            }
            else
            {
                $couldUpdateAll = false;
            }
        }

        return $couldUpdateAll;
    }

    /**
     * @deprecated
     */
    public function delete(): void
    {
        if (!$this->id)
        {
            throw new DatabaseError('No ID!');
        }

        $table = static::TABLE;
        /** @noinspection SqlResolve */
        DBConnection::getPDO()->executeQuery("DELETE FROM {$table} WHERE id = ?", [$this->id]);
    }

    /**
     * @deprecated
     */
    public function save(): bool
    {
        if (empty(static::TABLE))
        {
            throw new ImproperSubclassing('TABLE not properly set!');
        }

        $tableFields = $this->getTableFields();
        if (empty($tableFields))
        {
            throw new ImproperSubclassing('Table fields not properly set!');
        }

        // Create new
        if ($this->id === null)
        {
            $arguments = [];
            $placeholders = implode(',', array_fill(0, count($tableFields), '?'));
            foreach ($tableFields as $tableField)
            {
                $arguments[] = ValueConverter::phpToSql($this->$tableField);
            }

            $quotedTableFields = array_map(static fn (string $fieldName) => "`$fieldName`", $tableFields);
            $result = DBConnection::getPDO()->insert('INSERT INTO ' . static::TABLE . ' (' . implode(',', $quotedTableFields) . ') VALUES (' . $placeholders . ')', $arguments);
            if ($result !== false)
            {
                $this->id = (int)$result;
                $this->created = new DateTime();
                $this->modified = new DateTime();
            }
        }
        // Modify existing entry
        else
        {
            $setStrings = [];
            $arguments = [];

            foreach ($tableFields as $tableField)
            {
                $mangledField = ValueConverter::phpToSql($this->$tableField);
                if ($mangledField !== null)
                {
                    $setStrings[] = "`{$tableField}`=?";
                    $arguments[] = $mangledField;
                }
                else
                {
                    $setStrings[] = "`$tableField`= NULL";
                }
            }

            $arguments[] = $this->id;
            $result = DBConnection::getPDO()->insert('UPDATE ' . static::TABLE . ' SET ' . implode(',', $setStrings) . ' WHERE id=?', $arguments);
            if ($result !== false)
            {
                $this->modified = new DateTime();
            }
        }

        return $result !== false;
    }

    /**
     * @return static[]
     */
    public static function fetchAllForSelect(): array
    {
        $records = static::fetchAll();
        $ret = [];
        foreach ($records as $record)
        {
            $ret[$record->id] = $record;
        }

        return $ret;
    }
}
