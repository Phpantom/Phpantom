<?php

namespace Phpantom\Tests\Frontier;

use Phpantom\Frontier\FrontierInterface;
use Phpantom\Frontier\Mongo;
use Phpantom\Resource;
use Zend\Diactoros\Request;

class MongoTest extends \PHPUnit_Framework_TestCase
{
    static protected $mongo;

    public static function setUpBeforeClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_filters_test'))->drop();
        self::$mongo = new Mongo(new \MongoDB($client, 'mongo_frontier_test'));

    }

    public static function tearDownAfterClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_frontier_test'))->drop();
    }

    public function testQueue()
    {
        $this->assertEquals(0, self::$mongo->count());
        $resource = new Resource(new Request('/', 'GET'), 'foo');
        $resource2 = new Resource(new Request('/', 'GET'), 'bar');
        $resource3 = new Resource(new Request('/', 'GET'), 'baz');
        self::$mongo->populate($resource);
        self::$mongo->populate($resource2);

        $this->assertEquals(2, self::$mongo->count());
        $resourceFromFrontier = self::$mongo->nextResource();
        $this->assertEquals($resource->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource->getUrl(), $resourceFromFrontier->getUrl());
        $this->assertEquals(1, self::$mongo->count());

        self::$mongo->populate($resource3, FrontierInterface::PRIORITY_HIGH);

        $resourceFromFrontier = self::$mongo->nextResource();
        $this->assertEquals($resource3->getType(), $resourceFromFrontier->getType());
        $this->assertEquals($resource3->getUrl(), $resourceFromFrontier->getUrl());

        self::$mongo->clear();
        $this->assertEquals(0, self::$mongo->count());
    }
}
