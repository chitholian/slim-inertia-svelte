<?php

namespace App\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    /** @var \Closure[] */
    private array $serviceFactories = [];

    /** @var array */
    private array $services = [];

    /**
     * @param \Closure[] $serviceFactories
     */
    public function __construct(array $serviceFactories = [])
    {
        foreach ($serviceFactories as $serviceId => $serviceFactory) {
            $this->set($serviceId, $serviceFactory);
        }
    }

    public function set(string $serviceId, \Closure $serviceFactory)
    {
        $this->serviceFactories[$serviceId] = $serviceFactory;
        unset($this->services[$serviceId]);
    }

    public function configureFromFile(string $path)
    {
        foreach (require $path as $serviceId => $serviceFactory) {
            $this->set($serviceId, $serviceFactory);
        }
    }

    /**
     * @return mixed
     * @throws NotFoundExceptionInterface
     *
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw DiServiceNotFound::createFromServiceId($id);
        }

        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->serviceFactories[$id]($this);
        }

        return $this->services[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->serviceFactories[$id]);
    }
}
