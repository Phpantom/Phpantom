<?php

namespace Phpantom\Frontier;

use Phpantom\Resource;

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
     * @param \Phpantom\Resource|Resource $resource
     * @param int $priority
     * @return mixed
     */
    public function populate(Resource $resource, $priority = self::PRIORITY_NORMAL);

    /**
     * @return \Phpantom\Resource
     */
    public function nextResource();

    /**
     * @return mixed
     */
    public function clear();
}
