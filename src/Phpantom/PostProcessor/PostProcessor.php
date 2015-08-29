<?php

namespace Phpantom\PostProcessor;

use Phpantom\Document\DocumentInterface;

/**
 * Class PostProcessor
 * @package Phantom
 */
abstract class PostProcessor implements PostProcessorInterface
{
    /**
     * @var DocumentInterface
     */
    private $documentStorage;

    /**
     * @return mixed
     */
    public function getDocumentStorage()
    {
        return $this->documentStorage;
    }

    /**
     * @param array $document
     * @param $type
     * @param array $params
     * @return mixed
     */
    abstract public function process(array $document, $type, array $params = []);

    /**
     * @param DocumentInterface $storage
     */
    public function __construct(DocumentInterface $storage)
    {
        $this->documentStorage = $storage;
    }

    /**
     * @param $type
     * @param array $params
     */
    public function apply($type, array $params = [])
    {
        foreach($this->getDocumentStorage()->getIterator($type) as $doc) {
            $this->process($doc, $type, $params);
        }
    }
}
