<?php
use Phpantom\Processor\Middleware\Items;
use Phpantom\Processor\Middleware\Resources;
use Phpantom\Processor\Middleware\Blobs;

require 'vendor/autoload.php';

$stream = new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::DEBUG);
$formatter = new \Zoya\Monolog\Formatter\ColoredConsoleFormatter();
$stream->setFormatter($formatter);
$logger = new \Monolog\Logger('PHANTOM');
$logger->pushHandler($stream);

$queue = [
//    new \Phpantom\Client\Middleware\Cache\File(),
    new \Phpantom\Client\Middleware\RandomUserAgent(),
    (new \Phpantom\Client\Middleware\Delay())->setMin(1)->setMax(2),
    new \Phpantom\Client\Middleware\Guzzle()
//    new \Phpantom\Client\Middleware\Casper()
];

$relayBuilder = new \Relay\RelayBuilder();
$relay = $relayBuilder->newInstance($queue);

$client = new \Phpantom\Client\Client($relay);
$storage = new \MongoDB(new \MongoClient(), 'mongo_test');
$frontier = new Phpantom\Frontier\Mongo($storage);


$filter = new Phpantom\Filter\Mongo($storage);
$documentsStorage = new Phpantom\Document\Mongo($storage);
$resultsStorage = new Phpantom\ResultsStorage\Mongo($storage);
$filesystem = new \Gaufrette\Filesystem(new \Gaufrette\Adapter\Local('/tmp/test'));
$blobsStorage = new Phpantom\BlobsStorage\Storage(new \Phpantom\BlobsStorage\Adapter\Gaufrette($filesystem));

$resource = new \Phpantom\Resource(new \Zend\Diactoros\Request('http://www.onliner.by', 'GET'), 'list');
//$resource = $engine->createResource('http://httpbin.org/user-agent', 'agent');

class ListProcessor implements \Phpantom\Processor\Relay\MiddlewareInterface
{
    public function __invoke(\Phpantom\Resource $resource, \Phpantom\Response $response, \Phpantom\ResultSet $resultSet, callable $next = null)
    {
        $crawler = new \Phpantom\Crawler((string)$response->getContent());
        echo $crawler->filter('title')->text();
    }
}

class AgentProcessor implements \Phpantom\Processor\Relay\MiddlewareInterface
{

    public function __invoke(\Phpantom\Resource $resource, \Phpantom\Response $response, \Phpantom\ResultSet $resultSet, callable $next = null)
    {
        print_r(json_decode($response->getContent(), true));
    }
}

$scraper = new \Phpantom\Scraper();
$scraper->setDefaultHttpClient($client);

$queue = [
    new \Phpantom\Processor\Middleware\BlobsProcessor($blobsStorage, new \Phpantom\Document\Manager($documentsStorage)),
    new \Phpantom\Processor\Middleware\ItemsProcessor(new \Phpantom\Document\Manager($documentsStorage)),
    new ListProcessor()
];

$relayBuilder = new \Phpantom\Processor\Relay\RelayBuilder();
$processorRelay = $relayBuilder->newInstance($queue);
$processor = new \Phpantom\Processor\Processor($processorRelay);
$scraper->addProcessor('list', $processor);

$engine = new \Phpantom\Engine($scraper, $frontier, $filter, $resultsStorage, $logger);
$engine->clearFrontier();
$engine->populateFrontier($resource, \Phpantom\Frontier\FrontierInterface::PRIORITY_NORMAL, true);
$engine->run();
