<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use Cyndaron\Util\Util;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

use function is_scalar;
use function sprintf;
use function array_merge;
use function reset;
use function array_key_exists;
use function array_fill;
use function count;
use function implode;
use function is_bool;
use function is_int;
use function is_float;
use function assert;
use function is_a;
use function str_contains;

abstract class Model
{
    public const TABLE = '';
    // Override to include the fields for that particular model
    public const TABLE_FIELDS = [];

    public ?int $id;
    public DateTime $modified;
    public DateTime $created;

    final public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     * @throws Exception
     * @return static|null
     */
    public static function fetchById(int $id): ?Model
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
     * @return array
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
        $record = DBConnection::doQueryAndFetchFirstRow("SELECT * FROM {$table} WHERE id = ?", [$this->id]);
        if ($record === false)
        {
            return false;
        }
        foreach (self::getExtendedTableFields() as $tableField)
        {
            $this->$tableField = $this->convertSQLValueforProperty($tableField, $record[$tableField]);
        }
        return true;
    }

    /**
     * @param array $where
     * @param array $args
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
        $results = DBConnection::doQueryAndFetchAll('SELECT * FROM ' . static::TABLE . ' ' . $whereString . ' ' . $afterWhere, $args) ?: [];
        return self::DBResultsToModels($results);
    }

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
     * @param array $result
     * @return static
     */
    protected static function DBResultToModel(array $result): self
    {
        $obj = new static((int)$result['id']);
        $obj->updateFromArray($result);
        return $obj;
    }

    /**
     * @param array $where
     * @param array $args
     * @param string $afterWhere
     * @return static|null
     */
    public static function fetch(array $where = [], array $args = [], string $afterWhere = ''): ?self
    {
        $results = static::fetchAll($where, $args, $afterWhere);
        if (count($results) > 0)
        {
            $firstElem = reset($results);
            return $firstElem;
        }

        return null;
    }


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
     * @param array $array
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $sub = new static((int)$array['id']);
        $sub->updateFromArray($array);
        return $sub;
    }

    public function updateFromArray(array $newArray): bool
    {
        $couldUpdateAll = true;
        foreach (self::getExtendedTableFields() as $tableField)
        {
            if (array_key_exists($tableField, $newArray))
            {
                $this->$tableField = $this->convertSQLValueforProperty($tableField, $newArray[$tableField]);
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
        DBConnection::doQuery("DELETE FROM {$table} WHERE id = ?", [$this->id]);
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
                $arguments[] = $this->convertVarForSQL($this->$tableField);
            }

            $result = DBConnection::doQuery('INSERT INTO ' . static::TABLE . ' (' . implode(',', static::TABLE_FIELDS) . ') VALUES (' . $placeholders . ')', $arguments);
            if ($result !== false)
            {
                $this->id = (int)$result;
            }
        }
        // Modify existing entry
        else
        {
            $setStrings = [];
            $arguments = [];

            foreach (static::TABLE_FIELDS as $tableField)
            {
                $mangledField = $this->convertVarForSQL($this->$tableField);
                if ($mangledField !== null)
                {
                    $setStrings[] = $tableField . '=?';
                    $arguments[] = $mangledField;
                }
                else
                {
                    $setStrings[] = $tableField . '= NULL';
                }
            }

            $arguments[] = $this->id;
            $result = DBConnection::doQuery('UPDATE ' . static::TABLE . ' SET ' . implode(',', $setStrings) . ' WHERE id=?', $arguments);
        }

        return $result !== false;
    }

    private function convertVarForSQL(DateTimeInterface|string|float|int|bool|null $var): string|int|float|null
    {
        if ($var === null)
        {
            return null;
        }
        if (is_bool($var))
        {
            return (string)(int)$var;
        }
        if (is_int($var) || is_float($var))
        {
            return $var;
        }
        if ($var instanceof DateTimeInterface)
        {
            return $var->format(Util::SQL_DATE_TIME_FORMAT);
        }

        assert(is_scalar($var));
        // At this point, the value _has_ to be a string.
        return $var;
    }

    /**
     * @param string $property
     * @param mixed $var
     *
     * @throws \ReflectionException
     * @return string|int|float|bool|DateTimeInterface|null
     */
    private function convertSQLValueforProperty(string $property, mixed $var): DateTimeInterface|bool|int|float|string|null
    {
        if ($var === null)
        {
            return null;
        }

        $rp = new ReflectionProperty(static::class, $property);
        /** @var ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type */
        $type = $rp->getType();
        if ($type === null)
        {
            return $var;
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType)
        {
            $type = $type->getTypes()[0];
        }

        $typeName = $type->getName();
        switch ($typeName)
        {
            case 'int':
                return (int)$var;
            case 'float':
                return (float)$var;
            case 'bool':
                return (bool)(int)$var;
            case 'string':
                return $var;
        }
        if (is_a($typeName, DateTimeInterface::class, true))
        {
            $format = str_contains($var, ' ') ? Util::SQL_DATE_TIME_FORMAT : Util::SQL_DATE_FORMAT;

            // @phpstan-ignore-next-line
            return $typeName::createFromFormat($format, $var);
        }

        return $var;
    }

    public static function deleteById(int $id): bool
    {
        /** @noinspection SqlResolve */
        DBConnection::doQuery(sprintf('DELETE FROM %s WHERE id = ?', static::TABLE), [$id]);

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
