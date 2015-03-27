<?php

namespace Phpantom\Processor;

/**
 * Class Console
 * @package Phpantom\Processor
 */
class Console extends Processor
{
    /**
     * @param array $document
     * @param $type
     * @param array $params
     * @return mixed|void
     */
    public function process(array $document, $type, array $params = [])
    {
        print_r($document);
    }
}
