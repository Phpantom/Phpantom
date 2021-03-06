<?php

namespace Phpantom\Tests\Filter;

use Phpantom\Filter\InMemory;

class InMemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemory
     */
    static protected $storage;
    protected $resource;

    protected function setUp()
    {
        $resource = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();
        $resource->expects($this->any())->method('getHash')->will($this->returnValue(sha1(123)));
        $this->resource = $resource;
        self::$storage = new InMemory();
        self::$storage->add('foo', $resource);
    }

    protected function tearDown()
    {
        self::$storage->clear('foo');
    }

    public function testAddExist()
    {
        $this->assertTrue(self::$storage->exist('foo', $this->resource));
        $resource = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();
        $resource->expects($this->any())->method('getHash')->will($this->returnValue(sha1(555)));
        $this->assertFalse(self::$storage->exist('foo', $resource));
    }

    public function testRemoveCountClear()
    {
        $this->assertEquals(1, self::$storage->count('foo'));
        $resource = $this->getMockBuilder('\\Phpantom\\Resource')->disableOriginalConstructor()->getMock();
        $resource->expects($this->any())->method('getHash')->will($this->returnValue(sha1(555)));
        self::$storage->add('foo', $resource);
        $this->assertEquals(2, self::$storage->count('foo'));
        self::$storage->remove('foo', $resource);
        $this->assertEquals(1, self::$storage->count('foo'));
        self::$storage->clear('foo');
        $this->assertEquals(0, self::$storage->count('foo'));
    }
}
