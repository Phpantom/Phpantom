<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Interface ClientInterface
 * @package Phpantom
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function load(RequestInterface $request);

    /**
     * @param array $requests
     * @return mixed
     */
    public function loadBatch(array $requests);

    /**
     * @param Proxy $proxy
     * @return mixed
     */
    public function setProxy(Proxy $proxy);
}
