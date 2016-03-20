<?php

namespace Phpantom\Filter;

use Assert\Assertion;
use Phpantom\Resource;

/**
 * Class InMemory
 * @package Phantom\Filter
 */
class InMemory implements FilterInterface
{
    /**
     * @var array
     */
    private $hash = [];

    /**
     * @param string $filterName
     * @param \Phpantom\Resource $resource
     * @return mixed
     */
    public function add($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        $this->hash[$filterName][$resource->getHash()] = true;
    }

    /**
     * @param string $filterName
     * @param \Phpantom\Resource $resource
     * @return mixed
     */
    public function remove($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        unset($this->hash[$filterName][$resource->getHash()]);
    }

    /**
     * @param string $filterName
     * @param \Phpantom\Resource $resource
     * @return mixed
     */
    public function exists($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        return isset($this->hash[$filterName][$resource->getHash()]);
    }

    /**
     * @param string $filterName
     * @return mixed
     */
    public function clear($filterName)
    {
        Assertion::string($filterName);
        unset($this->hash[$filterName]);
    }

    /**
     * @param string $filterName
     * @return mixed
     */
    public function count($filterName)
    {
        Assertion::string($filterName);
        if (!isset($this->hash[$filterName])) {
            return 0;
        }
        return count($this->hash[$filterName]);
    }
}
