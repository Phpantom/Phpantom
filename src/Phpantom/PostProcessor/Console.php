<?php

namespace Phpantom\PostProcessor;

/**
 * Class Console
 * @package Phpantom\PostProcessor
 */
class Console extends PostProcessor
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
