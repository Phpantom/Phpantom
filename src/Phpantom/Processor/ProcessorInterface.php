<?php
namespace Phpantom\Processor;

use Phpantom\Resource;
use Phpantom\Response;
use Phpantom\ResultSet;

interface ProcessorInterface
{
    public function process(Resource $resource, Response $response, ResultSet $resultSet);
}
