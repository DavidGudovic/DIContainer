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


    protected array $services = [];
    protected array $instances = [];

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

    /**
     * @param string $key
     * @param callable|string $callback
     * @param bool $singleton
     * @return $this
     */
    public function register(string $key, callable|string $callback, bool $singleton = false): static
    {
        $this->services[$key] = $callback;

        if ($singleton) {
            $this->instances[$key] = null;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param Closure $callback
     * @return $this
     */
    public function singleton(string $key, Closure $callback): static
    {
        return $this->register($key, $callback, true);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ReflectionException
     */
    public function get(string $id)
    {
        if ($this->has($id)) {

            if(array_key_exists($id, $this->instances) && ! is_null($this->instances[$id])){
                return $this->instances[$id];
            }

            $serviceResolver = $this->services[$id];

            $resolvedService = $serviceResolver instanceof Closure
                ? $serviceResolver()
                : $serviceResolver;

            if (array_key_exists($id, $this->instances)) {
                $this->instances[$id] = $resolvedService;
            }

            return $resolvedService;
        }

        if (class_exists($id)) {
            return $this->build($id);
        }

        throw new CouldNotResolveClass();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
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
    private function build(string $service): object|string|null
    {
        try {
            $reflector = new ReflectionClass($service);
        } catch (ReflectionException $e) {
            throw new CouldNotResolveClass();
        }

        $parameters = $reflector->getConstructor()?->getParameters() ?? [];

        $resolvedDependencies = array_map(function (ReflectionParameter $parameter) {
            $class = $parameter->getType()->getName();

            if (class_exists($class)) {
                return $this->get($class);
            }
        }, $parameters);


        return $reflector->newInstanceArgs($resolvedDependencies);
    }
}
