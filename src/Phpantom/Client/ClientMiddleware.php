<?php

namespace Phpantom;

use Phpantom\Client\ClientInterface;

abstract class ClientMiddleware implements ClientInterface
{
    private $next;

    public function __construct(ClientInterface $client)
    {
        $this->next = $client;
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    abstract public function load(Resource $resource);

    public function getNext()
    {
        return $this->next;
    }

}
