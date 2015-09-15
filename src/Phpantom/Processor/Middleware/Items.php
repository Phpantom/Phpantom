<?php

namespace Phpantom\Processor\Middleware;

use Phpantom\Processor\ProcessorMiddleware;
use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

class Items extends ProcessorMiddleware
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
        foreach ($resultSet->getItems() as $item) {
            if ($this->getEngine()->documentExists($item->type, $item->id)) {
                $this->getEngine()->updateDocument($item->type, $item->id, $item->asArray());
            } else {
                $this->getEngine()->createDocument($item->type, $item->id, $item->asArray());
            }
        }
    }
}
