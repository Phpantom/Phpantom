<?php

namespace Phpantom\Frontier;

use Assert\Assertion;

/**
 * Class Mongo
 * @package Phpantom\Frontier
 */
class Mongo implements FrontierInterface
{
    private $frontierName = 'frontier';

    public function setFrontierName($name)
    {
        $this->frontierName = $name;
        return $this;
    }

    /**
     * @param \MongoDB $storage
     */
    public function __construct(\MongoDB $storage)
    {
        $this->storage = $storage;
        $this->setUp();
    }

    /**
     * @throws \Exception
     * @throws \MongoCursorException
     */
    protected function setUp()
    {
        try {
            $this->storage->{$this->getProjectFrontier() . '_counters'}->insert(array(
                '_id' => 'frontier',
                'seq' => 0,
            ));
        } catch (\MongoCursorException $e) {
            if ($e->getCode() !== 11000) {
                throw $e;
            }
        }

        $this->storage->{$this->getProjectFrontier()}->ensureIndex(array(
            'sec' => 1,
        ));
        $this->storage->{$this->getProjectFrontier()}->ensureIndex(array(
            'priority' => 1,
        ));
    }

    /**
     * @return mixed
     */
    protected function getNextSequence()
    {
        $ret = $this->storage->{$this->getProjectFrontier() . '_counters'}->findAndModify(
            array('_id' => 'frontier'),
            array('$inc' => array('seq' => 1)),
            null,
            array('new' => true)
        );

        return $ret['seq'];
    }

    /**
     * @return string
     */
    protected function getProjectFrontier()
    {
        return $this->frontierName;
    }

    /**
     * @param \Serializable $item
     * @param int $priority
     * @return mixed|void
     */
    public function populate(\Serializable $item, $priority = self::PRIORITY_NORMAL)
    {
        Assertion::integer($priority);
        $frontier = $this->getProjectFrontier();
        $this->storage->$frontier->save(
            ['sec' => $this->getNextSequence(), 'priority' => $priority, 'data' => serialize($item)]
        );

    }

    /**
     * @return \Serializable|null
     */
    public function nextItem()
    {
        $ret = $this->storage->{$this->getProjectFrontier()}->findAndModify(
            [],
            [],
            null,
            [
                'sort' => ['priority' => -1, 'seq' => 1],
                'remove' => true,
            ]
        );

        if (!$ret) {
            return null;
        }
        $item = unserialize($ret['data']);
        return $item;
    }

    /**
     *
     */
    public function clear()
    {
        $this->storage->{$this->getProjectFrontier()}->remove([]);
    }

}
