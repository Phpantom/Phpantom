<?php

namespace Phpantom\Frontier;

/**
 * Interface FrontierInterface
 * @package Phpantom
 */
interface FrontierInterface
{
    /**
     * Normal priority
     */
    const PRIORITY_NORMAL = 1;
    /**
     * High priority
     */
    const PRIORITY_HIGH = 2;

    /**
     * @param \Serializable $item
     * @param int $priority
     * @return mixed
     */
    public function populate(\Serializable $item, $priority = self::PRIORITY_NORMAL);

    /**
     * @return \Phpantom\Resource|null
     */
    public function nextItem();

    /**
     * @return mixed
     */
    public function clear();

}
