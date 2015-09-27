<?php
namespace Phpantom\Processor\Relay;

use ArrayObject;
use InvalidArgumentException;

/**
 *
 * A builder to create Relay objects.
 *
 * @package Relay.Relay
 *
 */
class RelayBuilder
{
    /**
     *
     * A callable to convert queue entries to callables in the Runner.
     *
     * @var callable|ResolverInterface
     *
     */
    protected $resolver;

    /**
     *
     * Constructor.
     *
     * @param callable|ResolverInterface $resolver A callable to convert a queue entry to
     *                                             a callable|MiddlewareInterface in the Runner.
     *
     * @return self
     *
     */
    public function __construct(callable $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     *
     * Creates a new Relay with the specified queue for its Runner objects.
     *
     * @param array|ArrayObject|GetArrayCopyInterface $queue The queue
     * specification.
     *
     * @return Relay
     *
     */
    public function newInstance($queue)
    {
        return new Relay($this->newRunnerFactory($queue));
    }

    /**
     *
     * Creates a new RunnerFactory with a specified queue.
     *
     * @param array|ArrayObject|GetArrayCopyInterface $queue The queue
     * specification.
     *
     * @return RunnerFactory
     *
     */
    protected function newRunnerFactory($queue)
    {
        return new RunnerFactory(
            $this->getArray($queue),
            $this->resolver
        );
    }

    /**
     *
     * Converts the queue specification to an array.
     *
     * @param array|ArrayObject|GetArrayCopyInterface $queue The queue
     * specification.
     *
     * @return array
     *
     */
    protected function getArray($queue)
    {
        if (is_array($queue)) {
            return $queue;
        }

        $getArrayCopy = $queue instanceof GetArrayCopyInterface
            || $queue instanceof ArrayObject;

        if ($getArrayCopy) {
            return $queue->getArrayCopy();
        }

        throw new InvalidArgumentException();
    }
}
