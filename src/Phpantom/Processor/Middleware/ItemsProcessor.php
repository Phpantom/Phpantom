<?php

namespace Phpantom\Processor\Middleware;

use Phpantom\Document\Manager;
use Phpantom\Item;
use Phpantom\Processor\Relay\MiddlewareInterface;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class ItemsProcessor implements MiddlewareInterface
{
    private $docManager;

    public function __construct(Manager $docManager)
    {
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
     * @param Resource $resource
     * @param Response $response
     * @param ResultSet $resultSet
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Resource $resource, Response $response, ResultSet $resultSet, callable $next = null)
    {
        $next($resource, $response, $resultSet);
        foreach ($resultSet->getItems() as $item) {
            /** @var Item $item */
            if ($this->getDocManager()->documentExists($item->type, $item->id)) {
                $this->getDocManager()->updateDocument($item->type, $item->id, $item->asArray());
            } else {
                $this->getDocManager()->createDocument($item->type, $item->id, $item->asArray());
            }
        }
    }
}
