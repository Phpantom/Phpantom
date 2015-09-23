<?php

namespace Phpantom\Client\Middleware;

use Assert\Assertion;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Relay\MiddlewareInterface;

class Delay implements MiddlewareInterface
{
    /**
     * min delay in microseconds
     * @var int
     */
    private $minDelay = 0;

    /**
     * max delay in microseconds
     * @var int
     */
    private $maxDelay = 10;

    /**
     * @param $min
     * @return $this
     */
    public function setMin($min)
    {
        Assertion::integer($min);
        $this->minDelay = $min;
        return $this;
    }

    /**
     * @param $max
     * @return $this
     */
    public function setMax($max)
    {
        Assertion::integer($max);
        $this->maxDelay = $max;
        return $this;
    }

    /**
     * @return int
     */
    private function getRandomDelay()
    {
        if ($this->minDelay == $this->maxDelay) {
            return $this->maxDelay;
        }
        return mt_rand($this->minDelay, $this->maxDelay);
    }

    /**
     * @param Request $request the request
     * @param Response $response the response
     * @param callable|MiddlewareInterface|null $next the next middleware
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $delay = $this->getRandomDelay();
        usleep($delay);
        return $next($request, $response);
    }
}
