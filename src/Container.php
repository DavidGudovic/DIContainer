<?php

namespace Container;

use Closure;
use Container\Exceptions\CouldNotResolveClass;
use JetBrains\PhpStorm\NoReturn;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container implements ContainerInterface
{
    protected static Container $instance;

    protected function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private array $services = [];

    public function register(string $keys, callable|string $value): static
    {
        $this->services[$keys] = $value;
        return $this;
    }

    /**
     * @param string $service
     * @return mixed
     */
    public function get(string $service)
    {
        if($this->has($service)){
            $service = $this->services[$service];

            if ($service instanceof Closure) {
                return $service();
            }

            return $service;
        }

        if(class_exists($service)){
            return $this->build($service);
        } else {
            throw new CouldNotResolveClass();
        }

    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /**
     * Dumps args and closes the script.
     *
     * @param ...$args
     * @return void
     */
    #[NoReturn] protected function dd(...$args): void
    {
        var_dump(...$args);
        die();
    }

    /**
     * Recursively resolves dependencies using Reflection API
     *
     * @param string $service
     * @return object|string|null
     * @throws ReflectionException
     */
    private function build(string $service)
    {
        try {
            $reflector = new ReflectionClass($service);
        } catch (ReflectionException $e) {
            throw new CouldNotResolveClass();
        }

        $parameters = $reflector->getConstructor()?->getParameters() ?? [];

        $resolvedDependencies = array_map(function (ReflectionParameter $parameter) {
            $class = $parameter->getType()->getName();

            if(class_exists($class)){
                return $this->get($class);
            }
        }, $parameters);


        return $reflector->newInstanceArgs($resolvedDependencies);
    }
}
