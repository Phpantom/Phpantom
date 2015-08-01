<?php

namespace Phpantom\Client;

use Phpantom\Rotator;

/**
 * Class Proxy
 * @package Phpantom\Client
 */
class Proxy
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

    public function nextProxy()
    {
        if (!empty($this->getProxyList())) {
            $proxy = $this->rotate([$this, 'getProxy']);
            return $proxy;
        }
        return null;
    }

    /**
     * !!!!May be public?
     * @return mixed
     */
    private function getProxy()
    {
        $this->getProxyList()->next();
        return $this->getProxyList()->current();
    }


}
