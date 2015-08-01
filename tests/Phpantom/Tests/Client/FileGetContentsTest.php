<?php

namespace Phpantom\Tests\Client;

use Phpantom\Client\FileGetContents;
use Phpantom\Client\Middleware\RandomUA;
use Phpantom\Client\Proxy;
use Zend\Diactoros\Request;
use Zend\Diactoros\Stream;

/**
 * Class FileGetContentsTest
 * @package Phpantom\Tests\Client
 */
class FileGetContentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test set and get methods for $timeout property
     */
    public function testSetGetTimeout()
    {
        $client = new FileGetContents();
        $timeout = 30;
        $client->setSiteout($timeout);
        $this->assertEquals($timeout, $client->getTimeout());
    }

    /**
     * Test set and get methods for $proxy property
     */
    public function testSetGetProxy()
    {
        $client = new FileGetContents();
        $proxy = new Proxy();
        $client->setProxy($proxy);
        $this->assertEquals($proxy, $client->getProxy());
    }

    /**
     * Test that nextProxy returns expected value
     */
    public function testNextProxy()
    {
        $client = new FileGetContents();
        $proxy = new Proxy();

        $client->setProxy($proxy);
        $this->assertNull($client->nextProxy());

        $proxyAddr = 'tcp://localhost:80';
        $proxy->setProxyList([$proxyAddr]);
        $this->assertEquals($proxyAddr, $client->nextProxy());
        $this->assertEquals($proxyAddr, $client->nextProxy());
    }


    public function testGetMethodLoad()
    {
        $client = new FileGetContents();
        $request = new Request('http://httpbin.org/get', 'GET');
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('http://httpbin.org/get', $data['url']);

    }

    public function testHeadersLoad()
    {
        $client = new FileGetContents();
        $request = new Request('http://httpbin.org/headers', 'GET');
        $request = $request->withAddedHeader('foo', 'bar')
            ->withAddedHeader('abc', ['123', '456']);
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $json = (string)$response->getBody();
        $data = json_decode($json, true)['headers'];
        $this->assertEquals('bar', $data['Foo']);
        $this->assertEquals('123, 456', $data['Abc']);
        $this->assertEquals('httpbin.org', $data['Host']);

    }

    public function testUserAgentMiddleware()
    {
        $request = new Request('http://httpbin.org/user-agent', 'GET');
        $client = new RandomUA(new FileGetContents());
        $client->setBrowserStrings(['Phpantom' => ['Phpantom client 1.0']]);
        $client->setBrowserFreq(['Phpantom' => 100]);
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $json = (string)$response->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('Phpantom client 1.0', $data['user-agent']);
    }

    public function testStatus()
    {
        $request = new Request('http://httpbin.org/status/418', 'GET');
        $client = new FileGetContents();
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("I'm a teapot", $response->getReasonPhrase());
    }

    public function testPostMethod()
    {
        $client = new FileGetContents();
        //default: Content-type: application/x-www-form-urlencoded
        $request = (new Request('http://httpbin.org/post', 'POST', fopen('php://temp', 'rw')));
        $request->getBody()->write(http_build_query(['foo' => 'bar', 'baz' => 'bin']));
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['form'];
        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals('bin', $data['baz']);

        //JSON
        $request = (new Request('http://httpbin.org/post', 'POST'))
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody(new Stream(fopen('php://temp', 'rw')));
        $request->getBody()
            ->write(json_encode(['foo' => 'bar', 'baz' => 'bin']));
        $response = $client->load($request);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['json'];
        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals('bin', $data['baz']);
    }


}
