<?php

namespace Phpantom\BlobsStorage;

use Phpantom\Resource;

/**
 * Interface AdapterInterface
 * @package Phpantom\BlobsStorage
 */
interface AdapterInterface
{

    /**
     * Returns an array of all keys (files and directories)
     *
     * @return array
     */
    public function keys();

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param string $contents
     * @return mixed
     */
    public function write(Resource $resource, $contents = '');

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function read(Resource $resource);

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function exists(Resource $resource);

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function delete(Resource $resource);

    public function clean();

}
