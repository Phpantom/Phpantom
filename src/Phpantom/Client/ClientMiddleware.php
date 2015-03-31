<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;

abstract class ClientMiddleware implements ClientMiddlewareInterface
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

    public function getNext()
    {
        return $this->next;
    }

}
