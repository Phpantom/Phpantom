<?php

namespace Phantom;

use Phpantom\Client\ClientInterface;
use Phpantom\Document\DocumentInterface;
use Phpantom\Engine;
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
     * @var Engine
     */
    private $engine;
    /**
     * @var string
     */
    protected $name;

    /**
     * @param ClientInterface $client
     * @param FrontierInterface $frontier
     * @param FilterInterface $filter
     * @param ResultsStorageInterface $resultsStorage
     * @param DocumentInterface $documentsStorage
     * @param LoggerInterface $logger
     */
    public function __construct(ClientInterface $client, FrontierInterface $frontier, FilterInterface $filter,
        ResultsStorageInterface $resultsStorage, DocumentInterface $documentsStorage, LoggerInterface $logger)
    {
        $this->name = array_pop(explode('\\', strtolower(get_class($this))));
        $this->engine = new \Phpantom\Engine($client, $frontier, $filter, $resultsStorage, $documentsStorage, $logger);

        $this->engine->setProject($this->name);
        //$engine = $this->engine;
//        $this->engine->addHandler(
//            'image',
//            function (Response $response, Resource $resource) use ($engine) {
//                $root = './blobs/' . $this->name . '/images';
//                $filesystem = new FileSystem($root);
//                $path = $filesystem->create($resource, $response->getContent(), $root, true);
//                $meta = $resource->getMeta();
//                $oldData = $engine->getDocument($meta['doc_type'], $meta['doc_id']);
//                if (isset($oldData['images'])) {
//                    $images = $oldData['images'];
//                    $images[] = $path;
//                    $images = array_unique($images);
//                } else {
//                    $images = [$path];
//                }
//                $engine->updateDocument($meta['doc_type'], $meta['doc_id'], ['images' => $images]);
//            }
//        );

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
                $this->getEngine()->clearDocs();
//                $root = './blobs/' . $this->name;
//                $this->getEngine()->clearBlobs($root);
                $this->initFrontier();
                break;
            case Engine::MODE_RESTART:
                $this->getEngine()->clearFailed();
                $this->getEngine()->clearFrontier();
                $this->initFrontier();
                break;
            case Engine::MODE_REFRESH_ONLY:
            case Engine::MODE_REFRESH_WITH_NEW:
                break;
        }

        //@todo handle refresh mode

        $this->registerHandlers();
        $this->registerEventHandlers();

        $engine = $this->getEngine();
        $engine->run();
    }

}
