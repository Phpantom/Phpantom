<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;

abstract class ClientMiddleware implements ClientInterface
{
    private $next;

    public function __construct(ClientInterface $client)
    {
        $this->next = $client;
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    abstract public function load(RequestInterface $request);

    /**
     * @param array $requests
     * @return mixed
     */
    abstract public function loadBatch(array $requests);

    /**
     * @return ClientInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param Proxy $proxy
     * @return mixed|void
     */
    public function setProxy(Proxy $proxy)
    {

    }

}
