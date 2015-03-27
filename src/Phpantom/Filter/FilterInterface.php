<?php

namespace Phpantom\Filter;

use Phpantom\Resource;

/**
 * Interface FilterInterface
 * @package Phpantom
 */
interface FilterInterface
{
    /**
     * @param string $filterName
     * @param \Phpantom\Resource $resource
     * @return mixed
     */
    public function add($filterName, Resource $resource);

    /**
     * @param string $filterName
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function remove($filterName, Resource $resource);

    /**
     * @param string $filterName
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function exist($filterName, Resource $resource);

    /**
     * @param string $filterName
     * @return mixed
     */
    public function clear($filterName);

    /**
     * @param string $filterName
     * @return mixed
     */
    public function count($filterName);
}
