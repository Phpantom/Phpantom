<?php

namespace Phpantom\Tests\Document;

use Phpantom\Document\Mongo;

class MongoTest extends \PHPUnit_Framework_TestCase
{
    static protected $mongo;
    static protected $initData = ['foo'=> 'bar', 'baz'=> '123'];


    public static function setUpBeforeClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_documents_test'))->drop();
        self::$mongo = new Mongo(new \MongoDB($client, 'mongo_documents_test'));
    }

    public static function tearDownAfterClass()
    {
        $client = new \MongoClient();
        (new \MongoDB($client, 'mongo_documents_test'))->drop();
    }

    protected function setUp()
    {
        self::$mongo->create('foo', '123abc', self::$initData);
    }

    protected function tearDown()
    {
        self::$mongo->clean();
    }

    public function testCreate()
    {
        $this->assertEquals(self::$initData, self::$mongo->get('foo', '123abc'));
    }

    public function testUpdate()
    {
        $newData = ['phpantom'=> 'scraper', 'foo'=>'hello'];
        self::$mongo->update('foo', '123abc', $newData);
        $updatedData = self::$mongo->get('foo', '123abc');
        $this->assertArrayHasKey('phpantom', $updatedData);
        $this->assertEquals($updatedData['phpantom'], 'scraper');
        $this->assertEquals($updatedData['foo'], 'hello');
    }

    public function testDelete()
    {
        self::$mongo->delete('foo', '123abc');
        $this->assertNull(self::$mongo->get('foo', '123abc'));
    }

    public function testGetIds()
    {
        self::$mongo->create('foo', '555', ['bar'=>'baz']);
        self::$mongo->create('page', '777', ['title'=>'hello']);

        $this->assertEquals(['123abc', '555'], self::$mongo->getIds('foo'));
    }

    public function testList()
    {
        $data = ['bar'=>'baz'];
        self::$mongo->create('foo', '555', $data);
        $list = self::$mongo->getList('foo');
        $this->assertInstanceOf('MongoCursor', $list);
        $docs = [];
        foreach ($list as $doc) {
            $docs[] = $doc;
        }
        $this->assertEquals([self::$initData, $data], $docs);
    }

    public function testGetIterator()
    {
        $data = ['bar'=>'baz'];
        self::$mongo->create('foo2', '555', $data);
        $iterator = self::$mongo->getIterator('foo');
        $this->assertInstanceOf('MongoCursor', $iterator);
        $docs = [];
        foreach ($iterator as $doc) {
            $docs[] = $doc;
        }
        $this->assertEquals([self::$initData], $docs, 'Documents with type foo');

        $iterator = self::$mongo->getIterator();
        $this->assertInstanceOf('MongoCursor', $iterator);
        $docs = [];
        foreach ($iterator as $doc) {
            $docs[] = $doc;
        }
        $this->assertEquals([self::$initData, $data], $docs, 'Documents with all types');
    }

    public function testCountAndClean()
    {
        $this->assertEquals(1, self::$mongo->count('foo'));
        $data = ['bar'=>'baz'];
        self::$mongo->create('foo2', '555', $data);
        $this->assertEquals(2, self::$mongo->count());
        self::$mongo->clean();
        $this->assertEquals(0, self::$mongo->count());
    }

    public function testGetTypes()
    {
        $data = ['bar'=>'baz'];
        self::$mongo->create('foo2', '555', $data);
        $this->assertEquals(['foo', 'foo2'], self::$mongo->getTypes());
    }
}
