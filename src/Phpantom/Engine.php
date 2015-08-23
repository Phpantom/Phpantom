<?php

namespace Phpantom;

use Assert\Assertion;
use Zend\Diactoros\Request;
use Phpantom\BlobsStorage\Storage;
use Phpantom\Client\ClientInterface;
use Phpantom\Document\DocumentInterface;
use Phpantom\Filter\FilterInterface;
use Phpantom\Frontier\FrontierInterface;
use Phpantom\ResultsStorage\ResultsStorageInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\NestedValidationExceptionInterface;


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
    const MODE_START = 'start';
    /**
     *
     */
    const MODE_RESTART = 'restart';
    /**
     *
     */
    const MODE_FULL_RESTART = 'full_restart';
    /**
     *
     */
    const MODE_REFRESH_ONLY = 'refresh_only';
    /**
     *
     */
    const MODE_REFRESH_WITH_NEW = 'refresh_with_new';

    /**
     *
     */
    const MODE_NEW_ONLY = 'new_only';

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
     * @var Storage
     */
    private $blobsStorage;

    /**
     * @var DocumentInterface
     */
    private $storage;
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var array
     */
    private $handlers = [];

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

    private $mode;


    /**
     * @param ClientInterface $client
     * @param FrontierInterface $frontier
     * @param FilterInterface $filter
     * @param ResultsStorageInterface $resultsStorage
     * @param BlobsStorage\Storage $blobsStorage
     * @param DocumentInterface $storage
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        FrontierInterface $frontier,
        FilterInterface $filter,
        ResultsStorageInterface $resultsStorage,
        Storage $blobsStorage,
        DocumentInterface $storage,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->frontier = $frontier;
        $this->filter = $filter;
        $this->resultsStorage = $resultsStorage;
        $this->blobsStorage = $blobsStorage;
        $this->storage = $storage;
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
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param array $handlers
     * @return $this
     */
    public function setHandlers($handlers)
    {
        $this->handlers = $handlers;
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
     * @param boolean $clearErrorsOnSuccess
     * @return $this
     */
    public function setClearErrorsOnSuccess($clearErrorsOnSuccess)
    {
        $this->clearErrorsOnSuccess = (bool)$clearErrorsOnSuccess;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param mixed $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return FrontierInterface
     */
    public function getFrontier()
    {
        return $this->frontier;
    }

    /**
     * @return \Phpantom\BlobsStorage\Storage
     */
    public function getBlobsStorage()
    {
        return $this->blobsStorage;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return DocumentInterface
     */
    public function getStorage()
    {
        return $this->storage;
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
     * @param $url
     * @param $type
     * @param string $method
     * @return Resource
     */
    public function createResource($url, $type, $method = 'GET')
    {
        $httpRequest = new Request($url, $method);
        return new Resource($httpRequest, $type);
    }

    /**
     * @param $docType
     * @param $docId
     * @param $url
     * @param $type
     * @param string $method
     * @return Resource
     */
    public function createBoundResource(
        $docType,
        $docId,
        $url,
        $type,
        $method = 'GET'
    ) {
        $boundResource = $this->createResource($url, $type, $method);
        $this->bindResourceToDoc($boundResource, $docType, $docId);
        return $boundResource;
    }


    public function getBoundDocument(Resource $resource)
    {
        $meta = $resource->getMeta();
        return $this->getDocument($meta['item_type'], $meta['item_id']);
    }

    /**
     * @param \Phpantom\Resource|Resource $baseResource
     * @param $url
     * @param $type
     * @param string $method
     * @return Resource
     */
    public function createRelatedResource(Resource $baseResource, $url, $type, $method = 'GET')
    {
        $httpRequest = new Request($url, $method, 'php://memory', ['Referer' => $baseResource->getUrl()]);
        $resource = new Resource($httpRequest, $type);
        return $resource;
    }


    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param $docType
     * @param $docId
     */
    public function bindResourceToDoc(Resource $resource, $docType, $docId)
    {
        $resource->setMeta(['item_id' => $docId, 'item_type' => $docType]);
    }


    /**
     * @param $type
     * @param $id
     * @param array $data
     */
    public function createDocument($type, $id, array $data)
    {
        $this->getStorage()->create($type, $id, $data);
    }

    public function updateBoundDocument(Resource $resource, array $data)
    {
        $meta = $resource->getMeta();
        $this->updateDocument($meta['item_type'], $meta['item_id'], $data);
    }

    /**
     * @param $type
     * @param $id
     * @param array $data
     */
    public function updateDocument($type, $id, array $data)
    {
        $this->getStorage()->update($type, $id, $data);
    }

    /**
     * @param $type
     * @param $id
     */
    public function deleteDocument($type, $id)
    {
        $this->getStorage()->delete($type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getDocument($type, $id)
    {
        return $this->getStorage()->get($type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return bool
     */
    public function documentExists($type, $id)
    {
        return $this->getStorage()->exists($type, $id);
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getDocumentsList($type)
    {
        return $this->getStorage()->getList($type);
    }

    /**
     * @param $type
     * @param callable $handler
     * @return $this
     */
    public function addHandler($type, callable $handler)
    {
        $this->handlers[$type] = $handler;
        return $this;
    }

    /**
     * @param $type
     * @return null
     */
    public function getHandler($type)
    {
        return isset($this->handlers[$type]) ? $this->handlers[$type] : null;
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
     * @param \Phpantom\Resource|Resource $resource
     */
    public function handleEvent($eventName, Response $response, Resource $resource)
    {
        foreach ($this->getEventHandlers($eventName) as $handler) {
            $handler($response, $resource);
        }
    }

    /**
     * @param Response $response
     * @param Resource $resource
     * @param \Exception $e
     */
    public function handleException(Response $response, Resource $resource, \Exception $e = null)
    {
        foreach ($this->getEventHandlers(self::EVENT_EXCEPTION) as $handler) {
            $handler($response, $resource, $e);
        }
    }

    /**
     * @param Response $response
     * @param Resource $resource
     * @param ResultSet $resultSet
     */
    public function handleSuccesResult(Response $response, Resource $resource, ResultSet $resultSet)
    {
        foreach ($this->getEventHandlers(self::EVENT_PARSE_SUCCESS) as $handler) {
            $handler($response, $resource, $resultSet);
        }
    }

    /**
     * Main entry point
     * @param string $mode
     */
    public function run($mode = self::MODE_START)
    {
        $this->mode = $mode;
        while ($resource = $this->currentResource = $this->getFrontier()->nextResource()) {
            $this->getLogger()->debug('Loading resource from URL ' . $resource->getUrl());
            $request = $resource->getHttpRequest();
            $httpResponse = $this->client->load($request);
            $response = new Response($httpResponse);

            if (($response->getStatusCode() === 200 || $response->getStatusCode() === 408)
                && strlen($response->getContent())
            ) {
                if ($this->clearErrorsOnSuccess) {
                    $this->httpFails = 0;
                }
                if ($this->httpFails > 0) {
                    $this->httpFails--;
                }
                $this->handleEvent(self::EVENT_FETCH_SUCCESS, $response, $resource);

                try {
                    if ($handler = $this->getHandler($resource->getType())) {
                        /** @var $handler callable */
                        try {
                            $resultSet = new ResultSet($resource);
                            $handler($response, $resource, $resultSet);
                            if ($resultSet->isBlob()) {
                                $path = $this->getBlobsStorage()->write($resource, $response->getContent());
                                $oldData = $this->getBoundDocument($resource);
                                $blobs = [];
                                if (isset($oldData['blobs'])) {
                                    $blobs = $oldData['blobs'];
                                }
                                $blobs[md5($resource->getUrl())] = $path;
                                $this->updateBoundDocument($resource, ['blobs' => $blobs]);
                            } else {
                                foreach ($resultSet->getResources() as $priority => $resData) {
                                    foreach ($resData as $newResourceData) {
                                        $this->populateFrontier(
                                            $newResourceData['resource'],
                                            $priority,
                                            $newResourceData['force']
                                        );
                                    }
                                }
                                foreach ($resultSet->getItems() as $item) {
                                    if ($this->documentExists($item->type, $item->id)) {
                                        $this->updateDocument($item->type, $item->id, $item->asArray());
                                    } else {
                                        $this->createDocument($item->type, $item->id, $item->asArray());
                                    }
                                }
                            }
                            $this->markParsed($resource);
                            $this->handleSuccesResult($response, $resource, $resultSet);
                        } catch (NestedValidationExceptionInterface $exception) {
                            $this->markNotParsed($resource, $exception->getFullMessage());
                            $this->handleEvent(self::EVENT_PARSE_FAILED, $response, $resource);
                            $this->handleException($response, $resource, $exception);
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
                $this->handleEvent(self::EVENT_FETCH_FAILED, $response, $resource);
                if ($this->httpFails > $this->maxHttpFails) {
                    $this->getLogger()->alert('Max number of http fails reached. Exit.');
                    die();
                }
            }
        }
    }

    /**
     * Populates Frontier with passed Resource.
     * @param \Phpantom\Resource|Resource $resource
     * @param int $priority High (2) or Normal (1)
     * @param bool $force Populate Frontier even if Resource is already visited
     */
    public function populateFrontier(Resource $resource, $priority = FrontierInterface::PRIORITY_NORMAL, $force = false)
    {
        Assertion::inArray($priority, [FrontierInterface::PRIORITY_NORMAL, FrontierInterface::PRIORITY_HIGH]);
        Assertion::boolean($force);
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
     * Clear all Documents
     */
    public function clearDocs()
    {
        $this->getStorage()->clean();
    }

    /**
     */
    public function clearBlobs()
    {
        $this->getBlobsStorage()->clean();
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
        return (bool)$this->getFilter()->exist($this->getProject() . 'visited', $resource);
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
        return (bool)$this->getFilter()->exist($this->getProject() . 'scheduled', $resource);

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

}
