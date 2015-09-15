<?php

namespace Phpantom\Processor;

use Phpantom\Engine;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

/**
 * Class ProcessorMiddleware
 * @package Phpantom\Processor
 */
abstract class ProcessorMiddleware implements ProcessorInterface
{
    /**
     * @var Engine
     */
    private $engine;
    /**
     * @var ProcessorInterface
     */
    private $next;

    /**
     * @param Engine $engine
     * @param ProcessorInterface $next
     */
    public function __construct(Engine $engine, ProcessorInterface $next)
    {
        $this->engine = $engine;
        $this->next = $next;
    }

    /**
     * @param Response $response
     * @param Resource $resource
     * @param ResultSet $resultSet
     * @return mixed
     */
    abstract public function process(Response $response, Resource $resource, ResultSet $resultSet);

    /**
     * @return ProcessorInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @return \Phpantom\Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

}
