<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Interface ClientMiddlewareInterface
 * @package Phpantom
 */
interface ClientMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function load(RequestInterface $request);
}
