<?php

namespace Phpantom\Frontier;

use Phpantom\Resource;

/**
 * Class InMemory
 * @package Phantom\Frontier
 */
class InMemory implements FrontierInterface
{

    private $queue;

    /**
     *
     */
    public function __construct(\SplPriorityQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @param Resource $resource
     * @param int $priority
     * @return mixed
     */
    public function populate(Resource $resource, $priority = self::PRIORITY_NORMAL)
    {
        $this->queue->insert($resource, $priority);
    }

    /**
     * @return Resource
     */
    public function nextResource()
    {
        if ($this->queue->valid()) {
            return $this->queue->extract();
        }
    }

    /**
     * @return mixed
     */
    public function clear()
    {
        $class = get_class($this->queue);
        $this->queue = new $class;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->queue->count();
    }
}
