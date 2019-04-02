<?php
declare (strict_types = 1);

namespace Cyndaron;

class Model
{
    protected $id;
    protected $record;
    protected static $table;
    const HAS_CATEGORY = false;

    public function __construct(?int $id)
    {
        $this->id = $id;
    }

    public static function loadFromDatabase(int $id)
    {
        $record = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM ' . static::$table . ' WHERE id = ?', [$id]);
        if ($record === false)
        {
            throw new \Exception('No such entry!');
        }
        // Needed to make sure an object of the derived class is returned, not one of the base class.
        $class = get_called_class();
        $object = new $class($id);
        $object->record = $record;
        return $object;
    }

    public static function fetchAll()
    {
        return DBConnection::doQueryAndFetchAll('SELECT * FROM ' . static::$table);
    }

    public function asArray(): array
    {
        return $this->record;
    }

    public function updateFromArray($newArray)
    {
        $this->record = array_merge($this->record, $newArray);
    }

    public function delete(): void
    {
        if (!$this->id)
        {
            throw new \Exception('No ID!');
        }

        DBConnection::doQuery('DELETE FROM ' . static::$table . ' WHERE id = ?', [$this->id]);
    }
}