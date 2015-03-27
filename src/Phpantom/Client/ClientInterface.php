<?php

namespace Phpantom\Client;

use Phpantom\Resource;

/**
 * Interface ClientInterface
 * @package Phpantom
 */
interface ClientInterface
{
    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function load(Resource $resource);
}
