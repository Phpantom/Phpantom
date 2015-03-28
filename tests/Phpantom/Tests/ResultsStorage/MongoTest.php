<?php

namespace Phpantom\Tests\ResultsStorage;

use Phpantom\ResultsStorage\Mongo;
use Phpantom\ResultsStorage\ResultsStorageInterface;

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

        $resource = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();
        $resource2 = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();
        $resource3 = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();

        self::$mongo->populate($resource, ResultsStorageInterface::STATUS_SUCCESS);
        self::$mongo->populate($resource2, ResultsStorageInterface::STATUS_FETCH_FAILED);

        $this->assertEquals(1, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
        $resourceFromFrontier = self::$mongo->nextResource(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals($resource, $resourceFromFrontier);
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
        $this->assertEquals(1, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$mongo->populate($resource3, ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(2, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));
        $resourceFromFrontier = self::$mongo->nextResource(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals($resource3, $resourceFromFrontier);

        self::$mongo->clear(ResultsStorageInterface::STATUS_FETCH_FAILED);
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_FETCH_FAILED));

        self::$mongo->clear(ResultsStorageInterface::STATUS_SUCCESS);
        $this->assertEquals(0, self::$mongo->count(ResultsStorageInterface::STATUS_SUCCESS));
    }
}
