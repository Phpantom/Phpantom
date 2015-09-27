<?php

namespace Phpantom\Processor;

use Phpantom\Processor\Relay\Relay;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class Processor implements ProcessorInterface
{
    /**
     * @var Relay
     */
    private $relay;

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


    public function process(Resource $resource, Response $response, ResultSet $resultSet)
    {
        $relay = $this->getRelay();
        return $relay($resource, $response, $resultSet);
    }
}