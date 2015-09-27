<?php

namespace Phpantom\Processor\Relay;

use Phpantom\Resource as Resource;
use Phpantom\Response as Response;
use Phpantom\ResultSet as ResultSet;

/**
 *
 * A single-use middleware dispatcher.
 *
 */
class Runner
{
    /**
     *
     * The middleware queue.
     *
     * @var (callable|MiddlewareInterface)[]
     *
     */
    protected $queue = [];

    /**
     *
     * A callable to convert queue entries to callables.
     *
     * @var callable|ResolverInterface
     *
     */
    protected $resolver;

    /**
     *
     * Constructor.
     *
     * @param (callable|mixed|MiddlewareInterface)[] $queue The middleware queue.
     *
     * @param callable|ResolverInterface $resolver Converts queue entries to callables.
     *
     * @return self
     *
     */
    public function __construct(array $queue, callable $resolver = null)
    {
        $this->queue = $queue;
        $this->resolver = $resolver;
    }

    /**
     *
     * Calls the next entry in the queue.
     *
     * @param Resource $resource
     * @param Response $response The outgoing response.
     * @param ResultSet $resultSet
     *
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet)
    {
        $entry = array_shift($this->queue);
        $middleware = $this->resolve($entry);
        return $middleware($resource, $response, $resultSet, $this);
    }

    /**
     *
     * Converts a queue entry to a callable, using the resolver if present.
     *
     * @param mixed|callable|MiddlewareInterface $entry The queue entry.
     *
     * @return callable|MiddlewareInterface
     *
     */
    protected function resolve($entry)
    {
        if (! $entry) {
            // the default callable when the queue is empty
            return function (
                Resource $resource, Response $response, ResultSet $resultSet,
                callable $next
            ) {
                return $resultSet;
            };
        }

        if (! $this->resolver) {
            return $entry;
        }

        return call_user_func($this->resolver, $entry);
    }
}
