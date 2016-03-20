<?php

namespace Phpantom\Client\Middleware;

use Phpantom\Client\Middleware\Cache\CacheInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Relay\MiddlewareInterface;

abstract class Cache implements CacheInterface, MiddlewareInterface
{
    /**
     * @var int The number of seconds in which the cached value will expire. 0 means never expire.
     */
    private $duration = 0;

    private $cacheableContentTypes = [
        'text/plain',
        'text/html',
        'text/css',
        'application/json',
        'text/javascript'
    ];

    /**
     * @param array $cacheableContentTypes
     * @return $this
     */
    public function setCacheableContentTypes($cacheableContentTypes)
    {
        $this->cacheableContentTypes = $cacheableContentTypes;
        return $this;
    }


    /**
     * @param Request $request the request
     * @param Response $response the response
     * @param callable|MiddlewareInterface|null $next the next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        if (null !== ($cachedResponse = $this->get($request))) {
            return $cachedResponse;
        }
        /** @var  Response $response */
        $response = $next($request, $response);
        if (200 === $response->getStatusCode() && strtoupper($request->getMethod()) === 'GET') {
            $contentType = $response->getHeaderLine('Content-Type');
            if (in_array($contentType, $this->cacheableContentTypes)) {
                $this->cache($request, $response);
            }
        }
        return $response;
    }

}
