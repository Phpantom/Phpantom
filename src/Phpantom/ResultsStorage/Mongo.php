<?php

namespace Phpantom\ResultsStorage;

use Assert\Assertion;
use Phpantom\Resource;

/**
 * Class Mongo
 * @package Phantom\ResultsStorage
 */
class Mongo implements ResultsStorageInterface
{
    private $prefix = 'results';

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param \MongoDB $storage
     */
    public function __construct(\MongoDB $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $status
     * @return string
     */
    protected function getProjectStorage($status)
    {
        return $this->prefix . ':' . $status;
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param string $status
     * @return mixed|void
     */
    public function populate( Resource $resource, $status = self::STATUS_SUCCESS)
    {
        $results = $this->getProjectStorage($status);
        $this->storage->$results->save(
            ['data'=>serialize($resource)]
        );
    }

    /**
     * @param string $status
     * @return mixed|null
     */
    public function nextResource($status)
    {
        Assertion::string($status);

        $results = $this->getProjectStorage($status);
        $ret = $this->storage->$results->findAndModify(
            [],
            [],
            null,
            [
                'remove' => true
            ]
        );
        if (!$ret) {
            return null;
        }
        $item = unserialize($ret['data']);
        return $item;

    }

    /**
     * @param string $status
     * @return mixed|void
     */
    public function clear($status)
    {
        Assertion::string($status);
        $results = $this->getProjectStorage($status);
        $this->storage->$results->remove([]);
    }
}
