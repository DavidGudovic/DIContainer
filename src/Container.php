<?php

namespace Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $bindings = [];
    public function bind(string $name, callable $binding): void
    {
        $this->bindings[$name] = $binding;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->bindings[$id]();
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }
}