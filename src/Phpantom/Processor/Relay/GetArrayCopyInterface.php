<?php
namespace Phpantom\Processor\Relay;

/**
 *
 * Allows queue-building objects to return an array copy.
 */
interface GetArrayCopyInterface
{
    /**
     *
     * Returns an array copy of the queue.
     *
     * @return array
     *
     */
    public function getArrayCopy();
}
