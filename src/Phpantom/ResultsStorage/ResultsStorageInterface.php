<?php

namespace Phpantom\ResultsStorage;

use Phpantom\Resource;

/**
 * Interface ResultsStorageInterface
 * @package Phantom
 */
interface ResultsStorageInterface
{
    /**
     *
     */
    const STATUS_SUCCESS = 'success';
    /**
     *
     */
    const STATUS_FETCH_FAILED = 'fetch_failed';
    /**
     *
     */
    const STATUS_PARSE_ERROR = 'parse_error';

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param string $status
     * @return mixed
     */
    public function populate(Resource $resource, $status = self::STATUS_SUCCESS);

    /**
     * @param $status
     * @return \Phpantom\Resource
     */
    public function nextResource($status);

    /**
     * @param $status
     * @return mixed
     */
    public function clear($status);
}
