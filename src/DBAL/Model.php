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

abstract class Model
{
    public const TABLE = '';
    // Override to include the fields for that particular model
    public const TABLE_FIELDS = [];

    public int|null $id;
    public DateTime $modified;
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
    public static function getExtendedTableFields(): array
    {
        return array_merge(static::TABLE_FIELDS, ['created', 'modified']);
    }

    public function loadIfIdIsSet(): bool
    {
        if ($this->id !== null)
        {
            return $this->load();
        }

        return false;
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
        foreach (self::getExtendedTableFields() as $tableField)
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
        foreach (self::getExtendedTableFields() as $tableField)
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
        foreach (self::getExtendedTableFields() as $tableField)
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

    public function save(): bool
    {
        if (empty(static::TABLE))
        {
            throw new ImproperSubclassing('TABLE not properly set!');
        }
        if (empty(static::TABLE_FIELDS))
        {
            throw new ImproperSubclassing('TABLE_FIELDS not properly set!');
        }

        // Create new
        if ($this->id === null)
        {
            $arguments = [];
            $placeholders = implode(',', array_fill(0, count(static::TABLE_FIELDS), '?'));
            foreach (static::TABLE_FIELDS as $tableField)
            {
                $arguments[] = ValueConverter::phpToSql($this->$tableField);
            }

            $quotedTableFields = array_map(static fn (string $fieldName) => "`$fieldName`", static::TABLE_FIELDS);
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

            foreach (static::TABLE_FIELDS as $tableField)
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

    public static function deleteById(int $id): bool
    {
        /** @noinspection SqlResolve */
        DBConnection::getPDO()->executeQuery(sprintf('DELETE FROM %s WHERE id = ?', static::TABLE), [$id]);

        return true;
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
