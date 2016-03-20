<?php

namespace Phpantom\Client\Middleware\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface CacheInterface
 * @package Phpantom\Client\Middleware\Cache
 */
interface CacheInterface
{
    /**
     * @param RequestInterface $resource
     * @return mixed
     */
    public function get(RequestInterface $resource);

    /**
     * @param RequestInterface $resource
     * @param ResponseInterface $response
     * @param int $duration
     * @return boolean
     */
    public function cache(RequestInterface $resource, ResponseInterface $response, $duration = 0);

    /**
     * @param RequestInterface $resource
     * @return mixed
     */
    public function clear(RequestInterface $resource);

    /**
     * @return mixed
     */
    public function purge();
}
