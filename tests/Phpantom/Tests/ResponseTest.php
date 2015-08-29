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
        //Fails:
        // $this->assertEquals($httpResponse->getBody()->getContents(), $unserialized->getBody()->getContents());
        $this->assertEquals((string) $httpResponse->getBody(), (string)$unserialized->getBody());
        $this->assertEquals('foo', (string) $unserialized->getBody());
        $this->assertEquals($httpResponse->getHeaders(), $unserialized->getHeaders());
        $this->assertEquals($httpResponse->getStatusCode(), $unserialized->getStatusCode());
    }
}