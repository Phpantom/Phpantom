<?php
namespace Phpantom\Processor;

use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

interface ProcessorInterface
{
    public function process(Response $response, Resource $resource, ResultSet $resultSet);
}
