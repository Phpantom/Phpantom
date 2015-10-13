<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Relay\Relay;

/**
 * To run scripts in parallel we can use:
 * parallel --xapply ::: 'sleep 5 && echo "trololo"' 'sleep 5 && echo "fff"' 'echo "assds"' ::: 1### 2### 3###
 * Class Client
 * @package Phpantom\Client
 */
class Client implements ClientInterface
{
    /**
     * @var Relay
     */
    private $relay;

    /**
     * @param Relay $relay
     */
    public function __construct(Relay $relay)
    {
        $this->relay = $relay;
    }

    /**
     * @return Relay
     */
    public function getRelay()
    {
        return $this->relay;
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     *
     */
    public function load(Request $request, Response $response)
    {
        $relay = $this->getRelay();
        return $relay($request, $response);
    }

}
