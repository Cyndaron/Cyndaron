<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Routing;

use function get_class;

final class DependencyInjectionContainer
{
    private array $objects = [];

    public function add(object $object): void
    {
        $className = get_class($object);
        $this->objects[$className] = $object;
    }

    public function get(string $className): ?object
    {
        return $this->objects[$className] ?? null;
    }
}
