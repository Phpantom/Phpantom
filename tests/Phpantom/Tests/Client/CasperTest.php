<?php
namespace Phpantom\Tests\Client;

use Phpantom\Client\Middleware\Casper;
use Phpantom\Client\Middleware\RandomUserAgent;
use Phpantom\Client\Proxy;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * Class FileGetContentsTest
 * @package Phpantom\Tests\Client
 */
class CasperTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetIsDebug()
    {
        $client = new Casper();
        $this->assertTrue($client->isDebug());
        $client->setDebug('false');
        $this->assertFalse($client->isDebug());
    }

    public function testSetGetOptions()
    {
        $client = new Casper();
        $this->assertInternalType('array', $client->getOptions());
        $this->assertEmpty($client->getOptions());
        $options = ['web-security' => 'no', 'cookies-file' => '/tmp/mycookies.txt'];
        $client->setOptions($options);
        $this->assertEquals($options, $client->getOptions());
    }

    public function testSetGetScript()
    {
        $client = new Casper();
        $this->assertEmpty($client->getScript());
        $script = 'casper.userAgent("Phpantom");';
        $client->setScript($script);
        $this->assertEquals($script, $client->getScript());
    }

    public function testSetGetProxy()
    {
        $client = new Casper();
        $proxy = new Proxy();
        $client->setProxy($proxy);
        $this->assertEquals($proxy, $client->getProxy());
    }

    /**
     * Test that nextProxy returns expected value
     */
    public function testNextProxy()
    {
        $client = new Casper();
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
        $client = new Casper();
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
        $client = new Casper();
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
        $this->assertEquals('123, 456', $data['Abc']);
        $this->assertEquals('httpbin.org', $data['Host']);
    }

//    public function testUserAgentMiddleware()
//    {
//        $request = new Request('http://httpbin.org/user-agent', 'GET');
//        $client = new RandomUserAgent(new Casper());
//        $client->setBrowserStrings(['Phpantom' => ['Phpantom client 1.0']]);
//        $client->setBrowserFreq(['Phpantom' => 100]);
//        $response = new Response();
//        $client($request, $response);
//        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
//        $this->assertEquals(200, $response->getStatusCode());
//
//        $json = (string)$response->getBody();
//        $data = json_decode($json, true);
//        $this->assertEquals('Phpantom client 1.0', $data['user-agent']);
//    }

    public function testStatus()
    {
        $request = new Request('http://httpbin.org/status/418', 'GET');
        $client = new Casper();
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("I'm a teapot", $response->getReasonPhrase());
    }

    public function testPostMethod()
    {
        $client = new Casper();
        //default:
//        $request = (new Request('http://httpbin.org/post', 'POST', fopen('php://temp', 'rw')));
//        $request->getBody()->write(json_encode(['foo' => 'bar', 'baz' => 'bin']));
//        $response = $client->load($request);
//        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
//        $this->assertEquals(200, $response->getStatusCode());
//        $json = (string)$response->getBody();
//        $data = json_decode($json, true)['data'];
//        $this->assertEquals('foo=bar&baz=bin', $data);

        //Form: Content-type: application/x-www-form-urlencoded
//        $proxy = new Proxy();
//        $proxy->setProxyList(['tcp://localhost:8888']);
//        $client->setProxy($proxy);
        $request = (new Request('http://httpbin.org/post', 'POST'))
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(new Stream(fopen('php://temp', 'rw')));
        $request->getBody()
            ->write(json_encode(['foo' => 'bar', 'baz' => 'bin']));
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $json = (string)$response->getBody();
        $data = json_decode($json, true)['form'];
        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals('bin', $data['baz']);

        //JSON
        $client = new Casper();
        $proxy = new Proxy();
        $proxy->setProxyList(['localhost:8888']);
        $client->setProxy($proxy);
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
        $client = new Casper();
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
        $client = new Casper();
        $request = new Request('http://httpbin.org/redirect-to?url=http://example.com/', 'GET');
        $response = new Response();
        $response = $client($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $html = (string)$response->getBody();
        $this->assertContains('Example Domain', $html);
    }

}
