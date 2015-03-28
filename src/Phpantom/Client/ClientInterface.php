<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;

/**
 * Interface ClientInterface
 * @package Phpantom
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function load(RequestInterface $request);
}
