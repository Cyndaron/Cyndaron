<?php

namespace Cyndaron;

use Cyndaron\Error\ImproperSubclassing;
use Cyndaron\Error\IncompleteData;
use Exception;
use ReflectionProperty;

class Model
{
    public const TABLE = '';
    public const HAS_CATEGORY = false;
    // Override to include the fields for that particular model
    public const TABLE_FIELDS = [];

    public ?int $id;
    public string $modified;
    public string $created;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     * @return static|null
     * @throws Exception
     */
    public static function loadFromDatabase(int $id): ?Model
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
            throw new IncompleteData('ID is not set!');
        }

        /** @noinspection SqlResolve */
        $record = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM ' . static::TABLE . ' WHERE id = ?', [$this->id]);
        if ($record === false)
        {
            return false;
        }
        foreach (self::getExtendedTableFields() as $tableField)
        {
            $this->$tableField = $record[$tableField];
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
        $results = DBConnection::doQueryAndFetchAll('SELECT * FROM ' . static::TABLE . ' ' . $whereString . ' ' . $afterWhere, $args);
        $ret = [];
        if ($results)
        {
            foreach ($results as $result)
            {
                $obj = new static((int)$result['id']);
                $obj->updateFromArray($result);
                $ret[] = $obj;
            }
        }

        return $ret;
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
            return reset($results);
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

    public function updateFromArray($newArray): bool
    {
        $couldUpdateAll = true;
        foreach (self::getExtendedTableFields() as $tableField)
        {
            if (array_key_exists($tableField, $newArray))
            {
                $this->$tableField = $newArray[$tableField];
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
            throw new IncompleteData('No ID!');
        }

        /** @noinspection SqlResolve */
        DBConnection::doQuery('DELETE FROM ' . static::TABLE . ' WHERE id = ?', [$this->id]);
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
                $arguments[] = $this->mangleVar($this->$tableField);
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
                $setStrings[] = $tableField . '=?';
                $arguments[] = $this->mangleVar($this->$tableField);
            }

            $arguments[] = $this->id;
            $result = DBConnection::doQuery('UPDATE ' . static::TABLE . ' SET ' . implode(',', $setStrings) . ' WHERE id=?', $arguments);
        }

        return $result !== false;
    }

    private function mangleVar($var): ?string
    {
        if ($var === null)
        {
            return null;
        }

        if (is_bool($var))
        {
            $var = (int)$var;
        }
        return (string)$var;
    }

    /**
     * @param string $property
     * @param string $var
     *
     * @return string|int|bool
     * @throws \ReflectionException
     */
    public static function mangleVarForProperty(string $property, string $var)
    {
        $rp = new ReflectionProperty(static::class, $property);
        $type = $rp->getType();
        if ($type === null)
        {
            return $var;
        }

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        switch ($type->getName())
        {
            case 'int':
                return (int)$var;
            case 'bool';
                return (bool)(int)$var;
        }

        return $var;
    }

    public static function deleteById(int $id): bool
    {
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