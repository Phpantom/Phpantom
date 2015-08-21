<?php
namespace Phpantom;

use Assert\Assertion;
use Phpantom\Frontier\FrontierInterface;

class BatchEngine extends Engine
{
    /**
     * @var array
     */
    private $currentBatch = [];

    /**
     * @param array $batch
     * @return $this
     */
    public function setCurrentBatch(array $batch)
    {
        $this->currentBatch = $batch;
        return $this;
    }

    /**
     * @param int $batchSize
     * @param string $mode
     */
    public function run($batchSize = 10, $mode = self::MODE_START)
    {
        Assertion::integer($batchSize);
        Assertion::min($batchSize, 2);
        $this->setMode($mode);
        $i = 0;
        $batch = [];
        while ($resource = $this->getFrontier()->nextResource()) {
            if ($i < $batchSize) {
                $batch[$resource->getHash()] = $resource;
                $i++;
                continue;
            } else {
                $i = 0;
                $batch = [];
                $this->handleBatch($batch);
            }
        }
        if (!empty($batch)) {
            $this->handleBatch($batch);
        }
        $this->currentBatch = [];
    }

    /**
     * @param $batch
     */
    protected function handleBatch($batch)
    {
        $this->setCurrentBatch($batch);
        $this->getLogger()->debug('Loading batch of resources');
        $httpResponses = $this->getClient()->loadBatch($batch);
        foreach ($httpResponses as $hash => $httpResponse) {
            $response = new Response($httpResponse);
            $resource = $batch[$hash];
            if (($response->getStatusCode() === 200 || $response->getStatusCode() === 408)
                && strlen($response->getContent())
            ) {
                if ($this->isClearErrorsOnSuccess()) {
                    $this->httpFails = 0;
                }
                if ($this->httpFails > 0) {
                    $this->httpFails--;
                }

                $this->handleEvent(self::EVENT_FETCH_SUCCESS, $response, $resource);

                try {
                    if ($handler = $this->getHandler($resource->getType())) {
                        /** @var $handler callable */
                        $handler($response, $resource);
                        $this->handleEvent(self::EVENT_PARSE_SUCCESS, $response, $resource);
                        $this->markParsed($resource);
                    }
                    $this->markVisited($resource);
                } catch (\Exception $e) {
                    $this->handleEvent(self::EVENT_EXCEPTION, $response, $resource, $e);
                    $this->markNotParsed($resource);
                }

            } else {
                $this->httpFails++;
                $this->handleEvent(self::EVENT_FETCH_FAILED, $response, $resource);
                $this->markFailed($resource, $response);
                if ($this->getHttpFails() > $this->getMaxHttpFails()) {
                    $this->getLogger()->alert('Max number of http fails reached. Exit.');
                    die();
                }
            }
        }
    }

    /**
     *
     */
    protected function registerShutdownFunction()
    {
        register_shutdown_function(
            function () {
                if (!empty($this->currentBatch)) {
                    foreach ($this->currentBatch as $resource) {
                        $this->populateFrontier($resource, FrontierInterface::PRIORITY_HIGH);
                    }
                }
            }
        );
    }
}
