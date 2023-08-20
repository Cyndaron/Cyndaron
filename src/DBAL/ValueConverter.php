<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use BackedEnum;
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
use function is_scalar;
use function str_contains;

final class ValueConverter
{
    public static function phpToSql(DateTimeInterface|string|float|int|bool|BackedEnum|null $var): string|int|float|null
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
        if ($var instanceof BackedEnum)
        {
            return $var->value;
        }
        if ($var instanceof DateTimeInterface)
        {
            return $var->format(Util::SQL_DATE_TIME_FORMAT);
        }

        assert(is_scalar($var));
        // At this point, the value _has_ to be a string.
        return $var;
    }

    public static function sqlToPhp(mixed $var, string $class, string $property):  DateTimeInterface|BackedEnum|bool|int|float|string|null
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
        if (is_a($typeName, DateTimeInterface::class, true))
        {
            if (!str_contains($var, ' '))
            {
                $var .= ' 00:00:00';
            }

            // @phpstan-ignore-next-line
            return $typeName::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $var);
        }
        if (is_a($typeName, BackedEnum::class, true))
        {
            return $typeName::from($var);
        }

        return $var;
    }
}
