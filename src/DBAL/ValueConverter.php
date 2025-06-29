<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use BackedEnum;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Util\Util;
use DateTimeInterface;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use function assert;
use function is_a;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function str_contains;

final class ValueConverter
{
    public static function phpToSql(DateTimeInterface|string|float|int|bool|BackedEnum|null|Model $var): string|int|float|null
    {
        if ($var instanceof Model)
        {
            return $var->id;
        }
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
        if ($var instanceof BackedEnum)
        {
            return $var->value;
        }
        if ($var instanceof DateTimeInterface)
        {
            return $var->format(Util::SQL_DATE_TIME_FORMAT);
        }

        // At this point, the value _has_ to be a string.
        return $var;
    }

    public static function sqlToPhp(GenericRepository $genericRepository, string|int|float|null $var, string $class, string $property):  DateTimeInterface|BackedEnum|Model|bool|int|float|string|null
    {
        if ($var === null)
        {
            return null;
        }

        $rp = new ReflectionProperty($class, $property);
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
        if (is_a($typeName, Model::class, true))
        {
            assert(!is_object($typeName));
            return $genericRepository->fetchById($typeName, (int)$var);
        }
        if (is_a($typeName, DateTimeInterface::class, true))
        {
            assert(is_string($var));
            if (!str_contains($var, ' '))
            {
                $var .= ' 00:00:00';
            }

            // @phpstan-ignore-next-line
            return $typeName::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $var);
        }
        if (is_a($typeName, BackedEnum::class, true))
        {
            assert(is_string($var));
            return $typeName::from($var);
        }

        return $var;
    }
}
