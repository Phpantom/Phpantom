<?php

namespace Phpantom\Client\Middleware;

use Assert\Assertion;
use Phpantom\Client\ClientMiddleware;
use Psr\Http\Message\RequestInterface;

class Delay extends ClientMiddleware
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
     * @param RequestInterface $resource
     * @return mixed
     */
    public function load(RequestInterface $resource)
    {
        $delay = $this->getRandomDelay();
        usleep($delay);
        $response = $this->getNext()->load($resource);
        return $response;
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

}
