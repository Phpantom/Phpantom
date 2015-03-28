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
     * @return mixed
     */
    public function cache(RequestInterface $resource, ResponseInterface $response);

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
