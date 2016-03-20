<?php

namespace Phpantom\Frontier;

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
     * @param \Serializable $item
     * @param int $priority
     * @return mixed
     */
    public function populate(\Serializable $item, $priority = self::PRIORITY_NORMAL)
    {
        $this->queue->insert($item, $priority);
    }

    /**
     * @return \Serializable
     */
    public function nextItem()
    {
        if ($this->queue->valid()) {
            return $this->queue->extract();
        }
        return null;
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
