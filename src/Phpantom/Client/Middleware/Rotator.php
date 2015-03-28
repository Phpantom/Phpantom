<?php

namespace Phpantom\Client\Middleware;

use Zoya\Coin\Batch;
use Zoya\Coin\CoinInterface;

/**
 * Class Rotator
 * @package Phpantom\Client\Middleware
 */
trait Rotator
{
    /**
     * @var CoinInterface
     */
    private $strategy;
    /**
     * @var
     */
    private $lastResult;

    /**
     * @param CoinInterface $strategy
     * @return $this
     */
    public function setStrategy(CoinInterface $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @return Batch|CoinInterface
     */
    public function getStrategy()
    {
        if (!isset($this->strategy)) {
            $this->strategy = new Batch();
        }
        return $this->strategy;
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function rotate(callable $callback)
    {
        $params = func_get_args();
        array_shift($params);
        if (!isset($this->lastResult)) {
            $this->lastResult = call_user_func_array($callback, $params);
        } else {
            $this->getStrategy()->flip();
            if ($this->getStrategy()->isLucky()) {
                $this->lastResult = call_user_func_array($callback, $params);
            }
        }
        return $this->lastResult;
    }
}
