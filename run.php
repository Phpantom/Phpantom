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

//$client = new Phpantom\Client\Casper();
$client = new \Phpantom\Client\Middleware\RandomUA( new Phpantom\Client\Guzzle());
//$client = new Phpantom\Client\FileGetContents();

$storage = new \MongoDB(new \MongoClient(), 'mongo_test');

$frontier = new Phpantom\Frontier\Mongo($storage);


//$resource = new Resource(
//    'http://jobs.tut.by/search/resume?text=&logic=normal&pos=full_text&exp_period=all_time&relocation=living_or_relocation&salary_from=&salary_to=&currency_code=BYR&education=none&age_from=&age_to=&gender=unknown&order_by=publication_time&search_period=0&items_on_page=100',
//    'list'
//);

$filter = new Phpantom\Filter\Mongo($storage);
$documentsStorage = new Phpantom\Document\Mongo($storage);

$resultsStorage = new Phpantom\ResultsStorage\Mongo($storage);

$filesystem = new \Gaufrette\Filesystem(new \Gaufrette\Adapter\Local('/tmp/test'));
$blobsStorage = new Phpantom\BlobsStorage\Storage(new \Phpantom\BlobsStorage\Adapter\Gaufrette($filesystem));

$engine = new \Phpantom\Engine($client, $frontier, $filter, $resultsStorage, $blobsStorage, $documentsStorage, $logger);

$engine->clearFrontier();
$resource = $engine->createResource('http://www.onliner.by', 'list');
$engine->populateFrontier($resource, \Phpantom\Frontier\FrontierInterface::PRIORITY_NORMAL, true);

class ListProcessor implements \Phpantom\Processor\ProcessorInterface
{

    public function process(\Phpantom\Response $response, \Phpantom\Resource $resource, \Phpantom\ResultSet $resultSet)
    {
        $crawler = new \Phpantom\Crawler((string)$response->getContent());
        echo $crawler->filter('title')->text();
    }
}

$engine->addProcessor('list', new Resources($engine, new Blobs($engine, new Items($engine, new ListProcessor()))));

$engine->run(\Phpantom\Engine::MODE_FULL_RESTART);
