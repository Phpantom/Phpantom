<?php

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialization()
    {
        $request = new \Zend\Diactoros\Request('http://example.com', 'GET');
        $resource = new \Phpantom\Resource($request, 'foo');
        $serialized = serialize($resource);
        $unserialized = unserialize($serialized);
        $this->assertEquals($resource->getType(), $unserialized->getType());
        $this->assertEquals($resource->getMeta(), $unserialized->getMeta());
        $this->assertEquals($resource->getHeaders(), $unserialized->getHeaders());
        $this->assertEquals($resource->getRequestTarget(), $unserialized->getRequestTarget());
    }
}