<?php

namespace Phpantom\Processor\Relay;

use Phpantom\Resource as Resource;
use Phpantom\Response as Response;
use Phpantom\ResultSet as ResultSet;

/**
 * This interface defines the middleware interface signature required by Relay.
 *
 * Implementing this is completely voluntary, it's mostly useful for indicating that
 * your class is middleware, and to ensure you type-hint the `__invoke()` method
 * signature correctly.
 */
interface MiddlewareInterface
{
    /**
     * @param Resource $resource
     * @param Response $response
     * @param ResultSet $resultSet
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet, callable $next = null);
}
