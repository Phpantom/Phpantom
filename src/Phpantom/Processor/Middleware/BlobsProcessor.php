<?php
namespace Phpantom\Processor\Middleware;

use Phpantom\BlobsStorage\Storage;
use Phpantom\Document\DocumentInterface;
use Phpantom\Document\Manager;
use Phpantom\Processor\Relay\MiddlewareInterface;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class BlobsProcessor implements MiddlewareInterface
{
    private $storage;
    private $docManager;

    public function __construct(Storage $storage, Manager $docManager)
    {
        $this->storage = $storage;
        $this->docManager = $docManager;
    }

    /**
     * @return Manager
     */
    public function getDocManager()
    {
        return $this->docManager;
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param Resource $resource
     * @param Response $response
     * @param ResultSet $resultSet
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet, callable $next = null)
    {
        $next($resource, $response, $resultSet);
        if ($resultSet->isBlob()) {
            $path = $this->getStorage()->write($resource, $response->getContent());
            $oldData = $this->getDocManager()->getBoundDocument($resource);
            $blobs = [];
            if (isset($oldData['blobs'])) {
                $blobs = $oldData['blobs'];
            }
            $blobs[md5($resource->getUrl())] = $path;
            $this->getDocManager()->updateBoundDocument($resource, ['blobs' => $blobs]);
        }
    }


}
