<?php

namespace Phpantom\Processor\Middleware;

use Phpantom\Processor\ProcessorMiddleware;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class Resources extends ProcessorMiddleware
{
    /**
     * @param Response $response
     * @param Resource $resource
     * @param ResultSet $resultSet
     * @return mixed
     */
    public function process(Response $response, Resource $resource, ResultSet $resultSet)
    {
        $this->getNext()->process($response, $resource, $resultSet);
        foreach ($resultSet->getResources() as $priority => $resData) {
            foreach ($resData as $newResourceData) {
                $this->getEngine()->populateFrontier(
                    $newResourceData['resource'],
                    $priority,
                    $newResourceData['force']
                );
            }
        }
    }
}
