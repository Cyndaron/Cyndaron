<?php
declare(strict_types=1);

namespace Cyndaron\DBAL\Repository;

use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\ValueConverter;
use function count;
use function reset;
use function array_key_exists;

trait GenericRepositoryTrait
{
    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int|null $id
     * @return T
     */
    public function fetchOrCreate(string $class, int|null $id): Model
    {
        if ($id === null)
        {
            return new $class();
        }

        $ret = $this->fetchById($class, $id);
        if ($ret === null)
        {
            return new $class($id);
        }

        return $ret;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @return T[]
     */
    public function fetchAllForSelect(string $class): array
    {
        $records = $this->fetchAll($class);
        $ret = [];
        foreach ($records as $record)
        {
            $ret[$record->id] = $record;
        }

        return $ret;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param int $id
     */
    public function deleteById(string $class, int $id): void
    {
        $model = new $class($id);
        $this->delete($model);
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return T|null
     */
    public function fetch(string $class, array $where = [], array $args = [], string $afterWhere = ''): Model|null
    {
        $results = $this->fetchAll($class, $where, $args, $afterWhere);
        if (count($results) > 0)
        {
            $firstElem = reset($results);
            return $firstElem;
        }

        return null;
    }

    /**
     * @param Model $model
     * @param array<string, float|int|string|null> $newArray
     * @return bool
     */
    private function updateFromArray(Model $model, array $newArray): bool
    {
        $couldUpdateAll = true;
        foreach ($model->getTableFields(true) as $tableField)
        {
            if (array_key_exists($tableField->dbName, $newArray))
            {
                $model->{$tableField->propertyName} = ValueConverter::sqlToPhp($this, $newArray[$tableField->dbName], $model::class, $tableField->propertyName);
            }
            else
            {
                $couldUpdateAll = false;
            }
        }

        return $couldUpdateAll;
    }

    /**
     * @template T of Model
     *
     * @param class-string<T> $class
     * @param array<string, float|int|string|null> $array
     * @return T
     */
    public function createFromArray(string $class, array $array): Model
    {
        $model = new $class((int)$array['id']);
        $this->updateFromArray($model, $array);
        return $model;
    }
}
