<?php

namespace Phpantom;

use Phpantom\Filter\FilterInterface;
use Phpantom\Frontier\FrontierInterface;
use Phpantom\ResultsStorage\ResultsStorageInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;


/**
 * Class Scenario
 * @package Phantom
 */
abstract class Scenario
{
    use LoggerAwareTrait;

    /**
     * @var Engine
     */
    private $engine;
    /**
     * @var string
     */
    protected $name;


    /**
     * @param Scraper $scraper
     * @param FrontierInterface $frontier
     * @param FilterInterface $filter
     * @param ResultsStorageInterface $resultsStorage
     * @param LoggerInterface $logger
     */
    public function __construct(Scraper $scraper, FrontierInterface $frontier, FilterInterface $filter,
        ResultsStorageInterface $resultsStorage, LoggerInterface $logger)
    {
        register_shutdown_function(
            function () {
                $this->unlock();
            }
        );
        $chunks = explode('\\', strtolower(get_class($this)));
        $this->name = array_pop($chunks);
        $this->logger = $logger;
        $this->engine = new Engine($scraper, $frontier, $filter, $resultsStorage, $logger);
        $this->engine->setProject($this->name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function setName()
    {
        return $this->name;
    }

    /**
     * Override this if you need to specify 'priority' or 'force'
     */
    public function initFrontier()
    {
        foreach ($this->getInitialFrontier() as $resource) {
            $this->getEngine()->populateFrontier($resource);
        }
    }

    /**
     * @return \Phpantom\Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @return array
     */
    abstract public function getInitialFrontier();

    /**
     * @return array
     */
    abstract public function registerHandlers();

    /**
     *
     */
    protected function registerEventHandlers()
    {

    }

    public function lock()
    {
        $lockFile = $this->getLockFile();
        if (!file_exists($lockFile)) {
            $this->logger->debug('Obtaining lock');
            file_put_contents($lockFile, time());
            $this->logger->debug('Scenario was successfully locked');
        } else {
            throw new \RuntimeException("Scenario {$this->getName()} is locked");
        }
    }

    public function unlock()
    {
        $lockFile = $this->getLockFile();
        if (file_exists($lockFile)) {
            $this->logger->debug('Releasing lock');
            if (@unlink($lockFile)) {
                $this->logger->debug("Scenario {$this->getName()} is unlocked");
            } else {
                $this->logger->critical("Can't release lock!");
            }
        }
    }

    protected function getLockFile()
    {
        return sys_get_temp_dir() . '/' .  $this->getName();
    }

    /**
     * @param $mode
     */
    public function run($mode)
    {
        switch ($mode) {
            case Engine::MODE_START:
                $this->initFrontier();
                break;
            case Engine::MODE_FULL_RESTART:
                $this->getEngine()->clearScheduled();
                $this->getEngine()->clearVisited();
                $this->getEngine()->clearFrontier();
                $this->getEngine()->clearSuccessful();
                $this->getEngine()->clearFailed();
                $this->initFrontier();
                break;
            case Engine::MODE_RESTART:
                $this->getEngine()->clearFailed();
                //@todo copy failed resources to frontier
                $this->getEngine()->clearFrontier();
                $this->initFrontier();
                break;
            case Engine::MODE_REFRESH_ONLY:
            case Engine::MODE_REFRESH_WITH_NEW:
                break;
            case Engine::MODE_NEW_ONLY:
                $this->getEngine()->clearScheduled();
                $this->initFrontier();
                break;
        }

        //@todo handle refresh mode

        $this->registerHandlers();
        $this->registerEventHandlers();

        $engine = $this->getEngine();
        try {
            $this->lock();
            $engine->run();
            $this->unlock();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            exit(1);
        }
    }
}
