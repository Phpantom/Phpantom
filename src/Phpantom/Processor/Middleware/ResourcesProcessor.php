<?php

namespace Phpantom\Processor\Middleware;

use Phpantom\Engine;
use Phpantom\Processor\Relay\MiddlewareInterface;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class Resources implements MiddlewareInterface
{
    private $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param Resource $resource
     * @param Response $response
     * @param ResultSet $resultSet
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet, callable $next = null)
    {
        $next($resource, $response, $resultSet);
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
