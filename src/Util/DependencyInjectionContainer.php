<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Util;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use function assert;
use function get_class;
use function var_dump;

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
        if ($className === self::class)
        {
            /** @var T|null $ret */
            $ret = $this;
            return $ret;
        }

        /** @var T|null $ret */
        $ret = $this->objects[$className] ?? null;
        return $ret;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     * @throws RuntimeException
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

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return mixed[]
     */
    private function getParams(ReflectionMethod $reflectionMethod): array
    {
        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            /** @var class-string $className */
            $className = ($type instanceof ReflectionNamedType) ? $type->getName() : '';

            $params[] = $this->tryGet($className);
        }
        return $params;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     * @throws RuntimeException
     * @return T
     */
    public function createClassWithDependencyInjection(string $className)
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null)
        {
            $ret = $reflectionClass->newInstanceWithoutConstructor();
        }
        else
        {
            $params = $this->getParams($constructor);
            $ret = $reflectionClass->newInstanceArgs($params);
        }

        return $ret;
    }

    public function callMethodWithDependencyInjection(object $object, string $method): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $params = $this->getParams($reflectionMethod);
        $ret = $reflectionMethod->invokeArgs($object, $params);
        return $ret;
    }

    public function callStaticMethodWithDependencyInjection(string $callable): mixed
    {
        $reflectionMethod = new \ReflectionMethod($callable);
        $params = $this->getParams($reflectionMethod);
        $ret = $reflectionMethod->invokeArgs(null, $params);
        return $ret;
    }
}
