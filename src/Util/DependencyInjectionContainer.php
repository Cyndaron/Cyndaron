<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Util;

use RuntimeException;
use function get_class;

final class DependencyInjectionContainer
{
    /** @var array<class-string, object> */
    private array $objects = [];

    /**
     * @param object $object
     * @param class-string|null $className
     * @return void
     */
    public function add(object $object, string|null $className = null): void
    {
        if ($className === null)
        {
            $className = get_class($object);
        }

        $this->objects[$className] = $object;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     * @return T|null
     */
    public function tryGet(string $className)
    {
        /** @var T|null $ret */
        $ret = $this->objects[$className] ?? null;
        return $ret;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     * @throws \RuntimeException
     * @return T
     */
    public function get(string $className)
    {
        $ret = $this->tryGet($className);
        if ($ret === null)
        {
            throw new RuntimeException('No such class: ' . $className);
        }

        return $ret;
    }
}
