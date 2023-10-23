<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Util;

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

    public function get(string $className):object|null
    {
        return $this->objects[$className] ?? null;
    }
}
