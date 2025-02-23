<?php

declare(strict_types=1);

namespace App\Infrastructure\Container;

class Container
{
    private array $services = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): object
    {
        if (!isset($this->services[$id])) {
            throw new \RuntimeException("Service $id not found in container");
        }
        return $this->services[$id];
    }
}