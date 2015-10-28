<?php

namespace Phpantom\ResultsStorage;

use Assert\Assertion;
use Phpantom\Resource;

/**
 * Class InMemory
 * @package Phantom\ResultsStorage
 */
class InMemory implements ResultsStorageInterface
{
    private $storage = [];

    /**
     * @param Resource $resource
     * @param string $status
     * @return mixed
     */
    public function populate(Resource $resource, $status = self::STATUS_SUCCESS)
    {
        Assertion::string($status);
        $this->storage[$status][] = $resource;
    }

    /**
     * @param $status
     * @return Resource
     */
    public function nextResource($status)
    {
        Assertion::string($status);
        if (empty($this->storage[$status])) {
            return null;
        }
        return array_shift($this->storage[$status]);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function clear($status)
    {
        Assertion::string($status);
        $this->storage[$status] = [];
    }

    /**
     * @param $status
     * @return int
     */
    public function count($status)
    {
        Assertion::string($status);
        if (!isset($this->storage[$status])) {
            return 0;
        }
        return count($this->storage[$status]);
    }
}
