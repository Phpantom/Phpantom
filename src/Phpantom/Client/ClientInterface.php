<?php

namespace Phpantom\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ClientInterface
 * @package Phpantom
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function load(RequestInterface $request, ResponseInterface $response);

    /**
     * @param array $requests
     * @param array $responses
     * @return mixed
     */
    public function loadBatch(array $requests, array $responses);

}
