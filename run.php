<?php

require 'vendor/autoload.php';

$stream = new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::DEBUG);
$formatter = new \Zoya\Monolog\Formatter\ColoredConsoleFormatter();
$stream->setFormatter($formatter);
$logger = new \Monolog\Logger('PHANTOM');
$logger->pushHandler($stream);

$client = new Phpantom\Client\Casper();

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

$resource = $engine->createResource('http://www.kisll.ru/site/products', 'list');
$engine->populateFrontier($resource, \Phpantom\Frontier\FrontierInterface::PRIORITY_NORMAL, true);

$engine->addHandler('list', function(\Phpantom\Response $response, \Phpantom\Resource $resource) use ($engine){
        $crawler = new \Phpantom\Crawler((string)$response->getBody());
        echo $crawler->filter('title')->text();
    });

$engine->run();