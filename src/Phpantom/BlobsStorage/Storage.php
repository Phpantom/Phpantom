<?php

namespace Phpantom\BlobsStorage;

use Assert\Assertion;
use Phpantom\Resource;

/**
 * Class Storage
 * @package Phpantom\BlobsStorage
 */
class Storage
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param string $contents
     * @param bool $overwrite
     * @return mixed
     */
    public function write(Resource $resource, $contents = '', $overwrite = true)
    {
        Assertion::string($contents);
        Assertion::boolean($overwrite);
        return $this->adapter->write($resource, $contents, $overwrite);
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function read(Resource $resource)
    {
        return $this->adapter->read($resource);
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function exists(Resource $resource)
    {
        return $this->adapter->exists($resource);
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     */
    public function delete(Resource $resource)
    {
        $this->adapter->delete($resource);
    }

    public function clear()
    {
        $this->adapter->clear();
    }
}
