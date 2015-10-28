<?php

namespace Phpantom\Tests\ResultsStorage;

use Phpantom\Resource;
use Phpantom\ResultsStorage\InMemory;
use Phpantom\ResultsStorage\ResultsStorageInterface;
use Zend\Diactoros\Request;

class InMemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemory
     */
    static protected $storage;

    public static function setUpBeforeClass()
    {
        self::$storage = new InMemory();
    }

    public static function tearDownAfterClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_results_storage_test'))->drop();
    }

    public function testQueue()
    {
        $this->assertEquals(0, self::$storage->count(ResultsStorageInterface::STATUS_SUCCESS));

        $resource = new Resource(new Request('/', 'GET'), 'foo');
        $resource2 = new Resource(new Request('/', 'GET'), 'bar');
        $resource3 = new Resource(new Request('/', 'GET'), 'baz');

        self::$storage->populate($resource, ResultsStorageInterface::STATUS_SUCCESS);
        self::$storage->populate($resource2, ResultsStorageInterface::STATUS_FETCH_FAILED);

        $this->assertEquals(1, self::$storage->count(ResultsStorageInterface::STATUS_SUCCESS));
        $resourceFromFrontier = self::$storage->nextResource(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals($resource->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource->getUrl(), $resourceFromFrontier->getUrl());
        $this->assertEquals(0, self::$storage->count(ResultsStorageInterface::STATUS_SUCCESS));
        $this->assertEquals(1, self::$storage->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$storage->populate($resource3, ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(2, self::$storage->count(ResultsStorageInterface::STATUS_FETCH_FAILED));
        $resourceFromFrontier = self::$storage->nextResource(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals($resource2->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource2->getUrl(), $resourceFromFrontier->getUrl());

        self::$storage->clear(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(0, self::$storage->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$storage->clear(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals(0, self::$storage->count(ResultsStorageInterface::STATUS_SUCCESS));
    }
}
