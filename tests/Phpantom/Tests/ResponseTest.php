<?php

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testSerialization()
    {
        $httpResponse = new \Zend\Diactoros\Response();
        $httpResponse->getBody()->write('foo');
        $response = new \Phpantom\Response($httpResponse);
        $serialized = serialize($response);
        $unserialized = unserialize($serialized);
        $this->assertEquals($httpResponse->getBody()->getContents(), $unserialized->getBody()->getContents());
        $this->assertEquals('foo', $unserialized->getBody()->getContents());
        $this->assertEquals($httpResponse->getHeaders(), $unserialized->getHeaders());
        $this->assertEquals($httpResponse->getStatusCode(), $unserialized->getStatusCode());
    }
}