<?php

namespace Phpantom\Client\Middleware;

use Phpantom\Client\Middleware\Cache\CacheInterface;
use Phpantom\Client\ClientMiddleware;
use Psr\Http\Message\RequestInterface;

abstract class Cache extends ClientMiddleware implements CacheInterface
{
    /**
     * @param RequestInterface $resource
     * @return mixed
     */
    public function load(RequestInterface $resource)
    {
        if (null !== ($response = $this->get($resource))) {
            return $response;
        }
        $response = $this->getNext()->load($resource);
        if (200 === $response->getStatusCode()) {
            $headers = $response->getHeaders();
            if (isset($headers['Content-Type'])
                && (
                    false !== strpos(
                        $response->getHeaders()['Content-Type'],
                        'text/'
                    ))
            ) {
                $this->cache($resource, $response);
            }

        }
        return $response;
    }
}
