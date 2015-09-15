<?php
namespace Phpantom\Processor\Middleware;

use Phpantom\BlobsStorage\Storage;
use Phpantom\Engine;
use Phpantom\Processor\ProcessorInterface;
use Phpantom\Processor\ProcessorMiddleware;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class Blobs extends ProcessorMiddleware
{
    /**
     * @param Response $response
     * @param Resource $resource
     * @param ResultSet $resultSet
     * @return mixed
     */
    public function process(Response $response, Resource $resource, ResultSet $resultSet)
    {
        $this->getNext()->process($response, $resource, $resultSet);
        if ($resultSet->isBlob()) {
            $path = $this->getEngine()->getBlobsStorage()->write($resource, $response->getContent());
            $oldData = $this->getEngine()->getBoundDocument($resource);
            $blobs = [];
            if (isset($oldData['blobs'])) {
                $blobs = $oldData['blobs'];
            }
            $blobs[md5($resource->getUrl())] = $path;
            $this->getEngine()->updateBoundDocument($resource, ['blobs' => $blobs]);
        }

    }
}
