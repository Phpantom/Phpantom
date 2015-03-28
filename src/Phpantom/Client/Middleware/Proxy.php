<?php

namespace Phpantom\Client\Middleware;

use Phpantom\ClientMiddleware;
use Psr\Http\Message\RequestInterface;

/**
 * Class Proxy
 * @package Phpantom\Client\Middleware
 */
class Proxy extends ClientMiddleware
{
    use Rotator;

    /**
     * @var \InfiniteIterator
     */
    private $proxyList;

    /**
     * @param array $proxyList
     */
    public function setProxyList(array $proxyList = [])
    {
        $proxyList = new \InfiniteIterator(new \ArrayIterator($proxyList));
        $this->proxyList = $proxyList;
        $this->proxyList->rewind();
    }

    /**
     * @return array
     */
    public function getProxyList()
    {
        return $this->proxyList;
    }


    /**
     * @todo Rewrite addMeta !!!
     * @param RequestInterface $resource
     * @return mixed
     */
    public function load(RequestInterface $resource)
    {
        if (!empty($this->getProxyList())) {
            $proxy = $this->rotate([$this, 'getProxy']);
            $resource->addMeta(['proxy' => $proxy]);
        }
        return $this->getNext()->load($resource);
    }

    /**
     * @return mixed
     */
    private function getProxy()
    {
        return $this->getProxyList()->next();
    }
}
