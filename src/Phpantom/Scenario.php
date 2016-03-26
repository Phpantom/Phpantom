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
    /**
     * Don't fetch visited urls
     */
    const MODE_NORMAL = 'normal';
    /**
     * Clear frontier and filters
     */
    const MODE_RESTART = 'restart';

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
    public function run($mode, $lock = false)
    {
        switch ($mode) {
            case self::MODE_NORMAL:
                $this->initFrontier();
                break;
            case self::MODE_RESTART:
                $this->getEngine()->clearAll();
                $this->initFrontier();
                break;
            default:
                throw new \RuntimeException(
                    sprintf('Unknown mode %s. Available modes: %s, %s', $mode, self::MODE_NORMAL, self::MODE_RESTART)
                );
        }

        //@todo handle refresh mode

        $this->registerHandlers();
        $this->registerEventHandlers();

        $engine = $this->getEngine();
        try {
            if ($lock) {
                $this->lock();
                $engine->run();
                $this->unlock();
            } else {
                $engine->run();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            exit(1);
        }
    }
}
