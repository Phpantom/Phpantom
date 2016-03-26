<?php

namespace Phpantom;

use Assert\Assertion;
use Respect\Validation\Exceptions\NestedValidationException;
use Zend\Diactoros\Response as HttpResponse;
use Zend\Diactoros\Request;
use Phpantom\Filter\FilterInterface;
use Phpantom\Frontier\FrontierInterface;
use Phpantom\ResultsStorage\ResultsStorageInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;


/**
 * Class Engine
 * @package Phantom
 */
class Engine
{
    use LoggerAwareTrait;

    /**
     *
     */
    const EVENT_FETCH_SUCCESS = 'fetch_success';
    /**
     *
     */
    const EVENT_FETCH_FAILED = 'fetch_failed';
    /**
     *
     */
    const EVENT_PARSE_SUCCESS = 'parse_success';
    /**
     *
     */
    const EVENT_PARSE_FAILED = 'parse_failed';
    /**
     *
     */
    const EVENT_EXCEPTION = 'exception';

    private $scraper;
    /**
     * @var
     */
    private $currentResource;
    /**
     * @var FrontierInterface
     */
    private $frontier;
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var ResultsStorageInterface
     */
    private $resultsStorage;

    /**
     * @var array
     */
    private $eventHandlers = [];

    /**
     * @var
     */
    private $project;

    /**
     * @var int
     */
    protected $httpFails = 0;
    /**
     * @var int
     */
    private $maxHttpFails = 3;
    /**
     * @var bool
     */
    private $clearErrorsOnSuccess = false;

    private $workerPoolSize;

    /**
     * @return int
     */
    public function getWorkerPoolSize()
    {
        return $this->workerPoolSize;
    }

    /**
     * @param int $workerPoolSize
     */
    public function setWorkerPoolSize($workerPoolSize)
    {
        $this->workerPoolSize = $workerPoolSize;
    }

    /**
     * @param Scraper $scraper
     * @param FrontierInterface $frontier
     * @param FilterInterface $filter
     * @param ResultsStorageInterface $resultsStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        Scraper $scraper,
        FrontierInterface $frontier,
        FilterInterface $filter,
        ResultsStorageInterface $resultsStorage,
        LoggerInterface $logger
    ) {
        $this->scraper = $scraper;
        $this->frontier = $frontier;
        $this->filter = $filter;
        $this->resultsStorage = $resultsStorage;
        $this->logger = $logger;
        $this->registerShutdownFunction();
    }

    protected function registerShutdownFunction()
    {
        register_shutdown_function(
            function () {
                if (!empty($this->currentResource)) {
                    $this->populateFrontier($this->currentResource, FrontierInterface::PRIORITY_HIGH);
                }
            }
        );
    }

    /**
     * @return Scraper
     */
    public function getScraper()
    {
        return $this->scraper;
    }


    /**
     * @return mixed
     */
    public function getCurrentResource()
    {
        return $this->currentResource;
    }

    /**
     * @param mixed $currentResource
     * @return $this
     */
    public function setCurrentResource($currentResource)
    {
        $this->currentResource = $currentResource;
        return $this;
    }

    /**
     * @return int
     */
    public function getHttpFails()
    {
        return $this->httpFails;
    }

    /**
     * @return boolean
     */
    public function isClearErrorsOnSuccess()
    {
        return $this->clearErrorsOnSuccess;
    }

    /**
     * @return $this
     */
    public function clearErrorsOnSuccess()
    {
        $this->clearErrorsOnSuccess = true;
        return $this;
    }

    public function notClearErrorsOnSuccess()
    {
        $this->clearErrorsOnSuccess = false;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return FrontierInterface
     */
    public function getFrontier()
    {
        return $this->frontier;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return ResultsStorageInterface
     */
    public function getResultsStorage()
    {
        return $this->resultsStorage;
    }

    /**
     * @param $project
     * @return $this
     */
    public function setProject($project)
    {
        Assertion::string($project);
        $this->project = $project;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return isset($this->project) ? (string)$this->project . ':' : '';
    }

    /**
     * @param $eventName
     * @param callable $handler
     * @return $this
     */
    public function registerEventHandler($eventName, callable $handler)
    {
        $this->eventHandlers[$eventName][] = $handler;
        return $this;
    }

    /**
     * @param $eventName
     * @return array
     */
    public function getEventHandlers($eventName)
    {
        return isset($this->eventHandlers[$eventName]) ? $this->eventHandlers[$eventName] : [];
    }

    /**
     * @param $eventName
     * @param Response $response
     * @param \Phpantom\Resource $resource
     * @param ResultSet $resultSet
     */
    public function handleEvent($eventName, Response $response, Resource $resource, ResultSet $resultSet)
    {
        foreach ($this->getEventHandlers($eventName) as $handler) {
            $handler($response, $resource, $resultSet);
        }
    }

    /**
     * @param Response $response
     * @param \Phpantom\Resource $resource
     * @param ResultSet $resultSet
     * @param \Exception $e
     */
    public function handleException(Response $response, Resource $resource, ResultSet $resultSet, \Exception $e = null)
    {
        foreach ($this->getEventHandlers(self::EVENT_EXCEPTION) as $handler) {
            $handler($response, $resource, $resultSet, $e);
        }
    }

    /**
     * @param Response $response
     * @param Resource $resource
     * @param ResultSet $resultSet
     */
    public function handleSuccessResult(Response $response, Resource $resource, ResultSet $resultSet)
    {
        foreach ($this->getEventHandlers(self::EVENT_PARSE_SUCCESS) as $handler) {
            $handler($response, $resource, $resultSet);
        }
    }

    public function processSingeItem(Resource $resource)
    {
        $this->getLogger()->debug('Loading resource from URL ' . $resource->getUrl());
        $request = $resource->getHttpRequest();
        $httpResponse = new HttpResponse();
        $httpResponse = $this->getScraper()
            ->getHttpClient($resource->getType())
            ->load($request, $httpResponse);
        $response = new Response($httpResponse);
        $resultSet = new ResultSet($resource);
        if (($response->getStatusCode() === 200 || $response->getStatusCode() === 408)
            && strlen($response->getContent())
        ) {
            if ($this->isClearErrorsOnSuccess()) {
                $this->httpFails = 0;
            }
            if ($this->httpFails > 0) {
                $this->httpFails--;
            }
            $this->handleEvent(self::EVENT_FETCH_SUCCESS, $response, $resource, $resultSet);
            try {
                if ($processor = $this->getScraper()->getProcessor($resource->getType())) {
                    /** @var $processor \Phpantom\Processor\ProcessorInterface */
                    try {
                        $processor->process($resource, $response, $resultSet);
                        $this->markParsed($resource);
                        $this->handleSuccessResult($response, $resource, $resultSet);
                    } catch (NestedValidationException $exception) {
                        $this->markNotParsed($resource, $exception->getFullMessage());
                        $this->handleEvent(self::EVENT_PARSE_FAILED, $response, $resource, $resultSet);
                        $this->handleException($response, $resource, $resultSet, $exception);
                    }
                }
                $this->markVisited($resource);
            } catch (\Exception $e) {
                $this->markNotParsed($resource, $e->getMessage());
                $this->handleException($response, $resource, $e);
            }

        } else {
            $this->httpFails++;
            $this->markFailed($resource, $response);
            $this->handleEvent(self::EVENT_FETCH_FAILED, $response, $resource, $resultSet);
            if ($this->httpFails > $this->maxHttpFails) {
                $this->getLogger()->alert('Max number of http fails reached. Exit.');
                die();
            }
        }
    }

    /**
     * Main entry point
     */
    public function run()
    {
        while ($item = $this->currentResource = $this->getFrontier()->nextItem()) {
            if ($item instanceof \Phpantom\Resource) {
                $this->processSingeItem($item);
            }
            if ($item instanceof \Phpantom\Batch) {
                $wp = new \QXS\WorkerPool\WorkerPool();
                $batchSize = $this->getWorkerPoolSize()? : $item->count();
                $wp->setWorkerPoolSize($batchSize)
                    ->create(new \QXS\WorkerPool\ClosureWorker(
                        /**
                         * @param mixed $input the input from the WorkerPool::run() Method
                         * @param \QXS\WorkerPool\Semaphore $semaphore the semaphore to synchronize calls accross all workers
                         * @param \ArrayObject $storage a persistent storage for the current child process
                         */
                            function ($item, $semaphore, $storage) {
                                $this->processSingeItem($item);
                                return $item;
                            }
                        )
                    );
                /** @var  $item \Phpantom\Batch */
                foreach ($item->getResources() as $resource) {
                    $wp->run($resource);
                }
                $wp->waitForAllWorkers(); // wait for all workers
            }
        }
    }

    /**
     * Populates Frontier with passed Resource.
     * @param \Phpantom\Resource | \Phpantom\Batch $item
     * @param int $priority High (2) or Normal (1)
     * @param bool $force Populate Frontier even if Resource is already visited
     */
    public function populateFrontier($item, $priority = FrontierInterface::PRIORITY_NORMAL, $force = false)
    {
        Assertion::inArray($priority, [FrontierInterface::PRIORITY_NORMAL, FrontierInterface::PRIORITY_HIGH]);
        Assertion::boolean($force);
        switch (true) {
            case $item instanceof \Phpantom\Resource:
                $this->populateSingleItem($item, $priority, $force);
                break;
            case $item instanceof \Phpantom\Batch:
                foreach($item->getResources() as $resource) {
                    if (!$this->isNewResource($resource)) {
                        $this->getLogger()->notice(
                            "Url {$resource->getUrl()} is already scheduled/visited. Removed from batch"
                        );
                        $item->removeResource($resource);
                    }
                }
                if ($item->count()) {
                    $this->getFrontier()->populate($item, $priority);
                } else {
                    $this->getLogger()->notice("Batch is empty. Skipped");
                }
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Instance of Phpantom\Resource or Phpantom\Batch expected. %s given', gettype($item)));
                break;
        }
    }

    private function isNewResource(Resource $resource, $force = false)
    {
        if ($this->isScheduled($resource) && !$force) {
            return false;
        }

        if ($this->isVisited($resource) && !$force && !$this->canContainLinksToNewResources($resource)) {
            return false;
        }
        return true;
    }

    private function populateSingleItem(Resource $resource, $priority = FrontierInterface::PRIORITY_NORMAL, $force = false)
    {
        if ($this->isScheduled($resource) && !$force) {
            $this->getLogger()->notice("Url {$resource->getUrl()} is already scheduled");
            return;
        }

        if ($this->isVisited($resource) && !$force && !$this->canContainLinksToNewResources($resource)) {
            $this->getLogger()->notice("Url {$resource->getUrl()} is already visited");
            return;
        }
        $this->getFrontier()->populate($resource, $priority);
        $this->markScheduled($resource);
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return bool
     */
    private function canContainLinksToNewResources(Resource $resource)
    {
        return (bool)preg_match('~!$~s', $resource->getType());
    }

    /**
     * Clear Frontier
     */
    public function clearFrontier()
    {
        $this->getFrontier()->clear();
        $this->getLogger()->debug('Cleared "frontier"');
    }

    /**
     * Clear filter of visited Resources
     */
    public function clearVisited()
    {
        $this->getFilter()->clear($this->getProject() . 'visited');
    }

    /**
     * Clear filter of scheduled Resources
     */
    public function clearScheduled()
    {
        $this->getFilter()->clear($this->getProject() . 'scheduled');
    }

    /**
     * Clear information about successfully scraped Resources
     */
    public function clearSuccessful()
    {
        $this->getFilter()->clear($this->getProject() . 'parsed');
        $this->getResultsStorage()->clear(ResultsStorageInterface::STATUS_SUCCESS);
        $this->getLogger()->debug('Cleared "visited" and "scheduled" filters');
    }

    /**
     * Clear information about failed Resources
     */
    public function clearFailed()
    {
        $this->getResultsStorage()->clear(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->getResultsStorage()->clear(ResultsStorageInterface::STATUS_PARSE_ERROR);
        $this->getLogger()->debug('Cleared "failed" and "not-parsed" filters');
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     */
    public function markVisited(Resource $resource)
    {
        $this->getFilter()->add($this->getProject() . 'visited', $resource);
        $this->getFilter()->remove($this->getProject() . 'scheduled', $resource);
        $this->getLogger()->debug(sprintf('Marked Resource %s as visited', $resource->getUrl()));
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return bool
     */
    public function isVisited(Resource $resource)
    {
        return (bool)$this->getFilter()->exists($this->getProject() . 'visited', $resource);
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     */
    public function markScheduled(Resource $resource)
    {
        $this->getFilter()->add($this->getProject() . 'scheduled', $resource);
        $this->getLogger()->debug(sprintf('Scheduled Resource %s for crawling', $resource->getUrl()));
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return bool
     */
    public function isScheduled(Resource $resource)
    {
        return (bool)$this->getFilter()->exists($this->getProject() . 'scheduled', $resource);

    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param Response $response
     */
    public function markFailed(Resource $resource, Response $response)
    {
        $this->markVisited($resource);
        $this->getResultsStorage()->populate($resource, ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->getLogger()->error(
            sprintf(
                'Marked Resource %s as failed. HTTP Status %s, content length %s',
                $resource->getUrl(),
                $response->getStatusCode(),
                strlen($response->getBody())
            )
        );
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     */
    public function markParsed(Resource $resource)
    {
        $this->getResultsStorage()->populate($resource, ResultsStorageInterface::STATUS_SUCCESS);
        $this->getLogger()->info(sprintf('Marked Resource %s as parsed.', $resource->getUrl()));
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     */
    public function markNotParsed(Resource $resource, $message = null)
    {
        $this->getFilter()->remove($this->getProject() . 'scheduled', $resource);
        $this->getResultsStorage()->populate($resource, ResultsStorageInterface::STATUS_PARSE_ERROR);
        $this->getLogger()->critical(
            sprintf('Marked Resource %s as NOT parsed. %s', $resource->getUrl(), is_null($message) ? '' : $message)
        );
    }

    /**
     * @param int $maxHttpFails
     * @return $this
     */
    public function setMaxHttpFails($maxHttpFails)
    {
        Assertion::integer($maxHttpFails);
        $this->maxHttpFails = $maxHttpFails;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxHttpFails()
    {
        return $this->maxHttpFails;
    }

    public function clearAll()
    {
        $this->clearVisited();
        $this->clearScheduled();
        $this->clearFrontier();
        $this->clearFailed();
        $this->clearSuccessful();
    }
}
