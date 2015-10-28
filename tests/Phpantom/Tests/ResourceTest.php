<?php

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialization()
    {
        $request = (new \Zend\Diactoros\Request('https://example.com', 'GET'))
        ->withRequestTarget('https://example.com');
        $resource = new \Phpantom\Resource($request, 'foo');
        $serialized = serialize($resource);
        $unserialized = unserialize($serialized);
        $this->assertEquals($resource->getUrl(), $unserialized->getUrl());
        $this->assertEquals($resource->getType(), $unserialized->getType());
        $this->assertEquals($resource->getMeta(), $unserialized->getMeta());
        $this->assertEquals($resource->getHeaders(), $unserialized->getHeaders());
        $this->assertEquals($resource->getRequestTarget(), $unserialized->getRequestTarget());
    }

}