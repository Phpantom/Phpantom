<?php

namespace Phpantom\Tests\ResultsStorage;

use Phpantom\Resource;
use Phpantom\ResultsStorage\Mongo;
use Phpantom\ResultsStorage\ResultsStorageInterface;
use Zend\Diactoros\Request;

class MongoTest extends \PHPUnit_Framework_TestCase
{
    static protected $mongo;

    public static function setUpBeforeClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_results_storage_test'))->drop();
        self::$mongo = new Mongo(new \MongoDB($client, 'mongo_results_storage_test'));

    }

    public static function tearDownAfterClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_results_storage_test'))->drop();
    }

    public function testQueue()
    {
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));

        $resource = new Resource(new Request('/', 'GET'), 'foo');
        $resource2 = new Resource(new Request('/', 'GET'), 'bar');
        $resource3 = new Resource(new Request('/', 'GET'), 'baz');

        self::$mongo->populate($resource, ResultsStorageInterface::STATUS_SUCCESS);
        self::$mongo->populate($resource2, ResultsStorageInterface::STATUS_FETCH_FAILED);

        $this->assertEquals(1, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
        $resourceFromFrontier = self::$mongo->nextResource(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals($resource->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource->getUrl(), $resourceFromFrontier->getUrl());
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
        $this->assertEquals(1, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$mongo->populate($resource3, ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(2, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));
        $resourceFromFrontier = self::$mongo->nextResource(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals($resource2->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource2->getUrl(), $resourceFromFrontier->getUrl());

        self::$mongo->clear(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$mongo->clear(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
    }
}
