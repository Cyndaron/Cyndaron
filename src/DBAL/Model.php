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
     * @return DatabaseFieldMapping[]
     */
    public function getTableFields(bool $extended = false): array
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
            $attributes = $property->getAttributes(DatabaseField::class);
            if (count($attributes) > 0)
            {
                /** @var DatabaseField $firstAttribute */
                $firstAttribute = $attributes[0]->newInstance();
                $ret[] = new DatabaseFieldMapping($name, $firstAttribute->dbName ?: $name);
            }
        }
        return $ret;
    }

    /**
     * @return array<string, float|int|string|null>
     */
    public function asArray(): array
    {
        $return = [];
        foreach ($this->getTableFields(true) as $tableField)
        {
            $return[$tableField->dbName] = $this->{$tableField->propertyName};
        }
        return $return;
    }
}
