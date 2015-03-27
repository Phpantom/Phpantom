<?php

namespace Phpantom\Processor;

/**
 * Interface ProcessorInterface
 * @package Phantom
 */
interface ProcessorInterface
{
    /**
     * @param array $document
     * @param $type
     * @param array $params
     * @return mixed
     */
    public function process(array $document, $type, array $params = []);
}
