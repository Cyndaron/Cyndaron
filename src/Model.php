<?php
declare (strict_types = 1);

namespace Cyndaron;

use Exception;

class Model
{
    const TABLE = null;
    const HAS_CATEGORY = false;
    // Override to include the fields for that particular model
    const TABLE_FIELDS = [];

    public $id;
    public $modified;
    public $created;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function loadFromDatabase(int $id)
    {
        // Needed to make sure an object of the derived class is returned, not one of the base class.
        $class = get_called_class();
        /** @var static $object */
        $object = new $class($id);
        if ($object->load())
        {
            return $object;
        }
        else
        {
            return null;
        }
    }

    public function loadIfIdIsSet(): bool
    {
        if ($this->id !== null)
        {
            return $this->load();
        }
        else
        {
            return false;
        }
    }

    public function load(): bool
    {
        if ($this->id === null)
        {
            throw new Exception('');
        }

        /** @noinspection SqlResolve */
        $record = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM ' . static::TABLE . ' WHERE id = ?', [$this->id]);
        if ($record === false)
        {
            return false;
        }
        foreach (static::TABLE_FIELDS as $tableField)
        {
            $this->$tableField = $record[$tableField];
        }
        return true;
    }

    public static function fetchAll()
    {
        return DBConnection::doQueryAndFetchAll('SELECT * FROM ' . static::TABLE);
    }

    public function asArray(): array
    {
        $return = [];
        foreach (static::TABLE_FIELDS as $tableField)
        {
            $return[$tableField] = $this->$tableField;
        }
        return $return;
    }

    public function updateFromArray($newArray)
    {
        foreach (static::TABLE_FIELDS as $tableField)
        {
            if (array_key_exists($tableField, $newArray))
                $this->$tableField = $newArray[$tableField];
        }
    }

    public function delete(): void
    {
        if (!$this->id)
        {
            throw new Exception('No ID!');
        }

        /** @noinspection SqlResolve */
        DBConnection::doQuery('DELETE FROM ' . static::TABLE . ' WHERE id = ?', [$this->id]);
    }

    public function save(): bool
    {
        if (empty(static::TABLE))
        {
            throw new Exception('TABLE not properly set!');
        }
        if (empty(static::TABLE_FIELDS))
        {
            throw new Exception('TABLE_FIELDS not properly set!');
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

            $result = DBConnection::doQuery('INSERT INTO ' . static::TABLE . ' (' . implode(',', static::TABLE_FIELDS) .  ') VALUES (' . $placeholders . ')', $arguments);
            if ($result !== false)
                $this->id = $result;
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

        return ($result === false) ? false : true;
    }

    private function mangleVar($var): string
    {
        if (is_bool($var))
            $var = (int)$var;
        return (string)$var;
    }
}