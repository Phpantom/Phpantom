<?php

namespace Phpantom\PostProcessor;

/**
 * Interface PostProcessorInterface
 * @package Phantom
 */
interface PostProcessorInterface
{
    /**
     * @param array $document
     * @param $type
     * @param array $params
     * @return mixed
     */
    public function process(array $document, $type, array $params = []);
}
