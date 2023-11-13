<?php

namespace Container;

use Closure;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected static Container $instance;

    protected function __construct(){}
    public static function getInstance(): static
    {
        if(!isset(self::$instance))
        {
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
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        $service = $this->services[$id];

        if ($service instanceof Closure) {
            return $service();
        }

        return $service;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}