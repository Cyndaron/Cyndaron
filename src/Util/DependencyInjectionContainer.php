<?php
/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Util;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
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
     * @template T of object
     *
     * @param class-string<T> $className
     * @return T
     */
    public function getOrCreate(string $className)
    {
        $ret = $this->tryGet($className);
        if ($ret !== null)
        {
            return $ret;
        }

        /** @var T $ret */
        $ret = $this->createClassWithDependencyInjection($className);
        return $ret;
    }

    /**
     * @param ReflectionMethod|ReflectionFunction $reflectionMethod
     * @return mixed[]
     */
    private function getParams(ReflectionMethod|ReflectionFunction $reflectionMethod): array
    {
        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            /** @var class-string $className */
            $className = ($type instanceof ReflectionNamedType) ? $type->getName() : '';

            try
            {
                $param = $this->getOrCreate($className);
                $params[] = $param;
            }
            catch (\Throwable $ex)
            {
                $parentClass = $reflectionMethod instanceof ReflectionMethod ? $reflectionMethod->class : '';
                $methodName = $reflectionMethod->getName();
                $message = "Cannot create argument for {$parentClass}::{$methodName} " . $ex->getMessage();
                throw new ReflectionException($message, $ex->getCode(), $ex->getPrevious());
            }

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

        $this->add($ret);
        return $ret;
    }

    public function callMethodWithDependencyInjection(object $object, string $method): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $params = $this->getParams($reflectionMethod);
        $ret = $reflectionMethod->invokeArgs($object, $params);
        return $ret;
    }

    public function callClosureWithDependencyInjection(Closure $closure): mixed
    {
        $reflectionFunction = new ReflectionFunction($closure);
        $params = $this->getParams($reflectionFunction);
        $ret = $reflectionFunction->invokeArgs($params);
        return $ret;
    }
}
