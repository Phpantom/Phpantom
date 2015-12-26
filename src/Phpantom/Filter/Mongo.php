<?php

namespace Phpantom\Filter;

use Assert\Assertion;
use Phpantom\Resource;

/**
 * @todo add prefix for fitername like for frontier and result storage
 * Class Mongo
 * @package Phantom\Filter
 */
class Mongo implements FilterInterface
{
    /**
     * @var \MongoDB
     */
    private $storage;

    /**
     * @param \MongoDB $storage
     */
    public function __construct(\MongoDB $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $filterName
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed|void
     */
    public function add($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        $this->storage->{$filterName}->save(
            ['_id' => $resource->getHash()]
        );
    }

    /**
     * @param string $filterName
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed|void
     */
    public function remove($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        $this->storage->{$filterName}->remove(
            ['_id' => $resource->getHash()],
                ['justOne' => true]
        );
    }

    /**
     * @param string $filterName
     * @param \Phpantom\Resource|Resource $resource
     * @return bool
     */
    public function exists($filterName, Resource $resource)
    {
        Assertion::string($filterName);
        $exist = $this->storage->{$filterName}->findOne(
            ['_id' => $resource->getHash()], ['_id']
        );
        return !empty($exist);
    }

    /**
     * @param string $filterName
     * @return mixed|void
     */
    public function clear($filterName)
    {
        Assertion::string($filterName);
        $this->storage->{$filterName}->remove([]);
    }

    /**
     * @param string $filterName
     * @return int
     */
    public function count($filterName)
    {
        return $this->storage->{$filterName}->count();
    }
}
