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
        $object = new static($id);
        if ($object->load())
        {
            return $object;
        }
        else
        {
            return null;
        }
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
    public static function fetchAll(array $where = [], array $args = [], string $afterWhere = '')
    {
        $whereString = '';
        if (count($where) > 0)
        {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }
        $results = DBConnection::doQueryAndFetchAll('SELECT * FROM ' . static::TABLE . ' ' . $whereString . ' ' . $afterWhere, $args);
        $ret = [];
        foreach ($results as $result)
        {
            $obj = new static((int)$result['id']);
            $obj->updateFromArray($result);
            $ret[] = $obj;
        }
        return $ret;
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

    public function updateFromArray($newArray)
    {
        foreach (self::getExtendedTableFields() as $tableField)
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

    private function mangleVar($var): ?string
    {
        if ($var === null)
            return null;

        if (is_bool($var))
            $var = (int)$var;
        return (string)$var;
    }
}