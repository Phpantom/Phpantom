<?php

namespace Phpantom;

use Phpantom\Client\ClientInterface;
use Phpantom\Processor\ProcessorInterface;

/**
 * Class Scraper
 * @package Phpantom
 */
class Scraper
{
    /**
     * @var array
     */
    private $httpClients = [];
    /**
     * @var array
     */
    private $processors = [];
    /**
     * @var
     */
    private $defaultHttpClient;
    /**
     * @var
     */
    private $defaultProcessor;

    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function setDefaultHttpClient(ClientInterface $client)
    {
        $this->defaultHttpClient = $client;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultHttpClient()
    {
        return $this->defaultHttpClient;
    }

    /**
     * @param $type
     * @param ClientInterface $client
     * @return $this
     */
    public function addHttpClient($type, ClientInterface $client)
    {
        $this->httpClients[$type] = $client;
        return $this;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getHttpClient($type)
    {
        return isset($this->httpClients[$type])? $this->httpClients[$type] : $this->getDefaultHttpClient();
    }

    /**
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function setDefaultProcessor(ProcessorInterface $processor)
    {
        $this->defaultProcessor = $processor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultProcessor()
    {
        return $this->defaultProcessor;
    }

    /**
     * @param $type
     * @param ProcessorInterface $processor
     * @return $this
     */
    public function addProcessor($type, ProcessorInterface $processor)
    {
        $this->processors[$type] = $processor;
        return $this;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getProcessor($type)
    {
        return isset($this->processors[$type])? $this->processors[$type] : $this->getDefaultProcessor();
    }

}
