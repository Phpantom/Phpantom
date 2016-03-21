<?php

namespace Phpantom\Tests\Client;

use Phpantom\Client\Middleware\Guzzle;
use Phpantom\Client\Proxy;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class GuzzleTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetConfig()
    {
        $client = new Guzzle();
        $defaultConfig = [
            'allow_redirects' => [
                'max'       => 5,
                'protocols' => ['http', 'https'],
                'strict'    => false,
                'referer'   => true
            ],
            'http_errors'     => true,
            'decode_content'  => true,
            'verify'          => true,
            'cookies'         => true
        ];
        $this->assertEquals($defaultConfig, $client->getConfig());
        $config = [
            'defaults' => [
                'timeout' => 100,
                'cookies' => false,
            ]
        ];
        $client->setConfig($config);
        $this->assertEquals($config, $client->getConfig());
    }

    public function testSetGetProxy()
    {
        $client = new Guzzle();
        $proxy = new Proxy();
        $client->setProxy($proxy);
        $this->assertEquals($proxy, $client->getProxy());
    }

    /**
     * Test that nextProxy returns expected value
     */
    public function testNextProxy()
    {
        $client = new Guzzle();
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
        $client = new Guzzle();
        $request = new Request('http://httpbin.org/get', 'GET');
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true);
        $this->assertEquals('http://httpbin.org/get', $data['url']);

    }


    public function testHeadersLoad()
    {
        $client = new Guzzle();
        $request = new Request('http://httpbin.org/headers', 'GET');
        $request = $request->withAddedHeader('foo', 'bar')
            ->withAddedHeader('abc', ['123', '456']);
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $json = (string)$response->getBody();
        $data = json_decode($json, true)['headers'];
        $this->assertEquals('bar', $data['Foo']);
        $this->assertEquals('123,456', $data['Abc']);
        $this->assertEquals('httpbin.org', $data['Host']);
    }

    public function testStatus()
    {
        $request = new Request('http://httpbin.org/status/418', 'GET');
        $client = new Guzzle();
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("I'm a teapot", $response->getReasonPhrase());
    }

    public function testPostMethod()
    {
        $client = new Guzzle();
        //default:
        $request = (new Request('http://httpbin.org/post', 'POST', fopen('php://temp', 'rw')));
        $request->getBody()->write(http_build_query(['foo' => 'bar', 'baz' => 'bin']));
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['data'];
        $this->assertEquals('foo=bar&baz=bin', $data);

        //Form: Content-type: application/x-www-form-urlencoded
        $request = (new Request('http://httpbin.org/post', 'POST'))
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(new Stream(fopen('php://temp', 'rw')));
        $request->getBody()
            ->write(http_build_query(['foo' => 'bar', 'baz' => 'bin']));
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['form'];
        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals('bin', $data['baz']);

        $request = (new Request('http://httpbin.org/post', 'POST'))
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody(new Stream(fopen('php://temp', 'rw')));
        $request->getBody()
            ->write(json_encode(['foo' => 'bar', 'baz' => 'bin']));
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['json'];
        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals('bin', $data['baz']);
    }

    public function testCookie()
    {
        $client = new Guzzle();
        $request = new Request('http://httpbin.org/cookies/set?k1=v1&k2=v2', 'GET');
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['cookies'];
        $this->assertEquals('v1', $data['k1']);
        $this->assertEquals('v2', $data['k2']);
    }

    public function testRedirect()
    {
        $client = new Guzzle();
        $request = new Request('http://httpbin.org/redirect-to?url=http://example.com/', 'GET');
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertContains('Example Domain', $html);
    }

}
