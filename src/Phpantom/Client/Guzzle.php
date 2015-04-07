<?php

namespace Phpantom\Client;

use GuzzleHttp\Client;
use Phly\Http\Response as HttpResponse;
use Psr\Http\Message\RequestInterface;

/**
 * Class Guzzle
 * @package Phantom\Client
 */
class Guzzle implements ClientInterface
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!isset ($this->client)) {
            $this->client = new Client([
                    'defaults' => [
                        'timeout' => 10,
                        'allow_redirects' => [
                            'max' => 5,
                            'strict' => false,
                            'referer' => true,
                            'protocols' => ['http', 'https']
                        ],
                        //'proxy'   => 'tcp://localhost:8888',
                        'cookies' => true,
                        'stream' => false,
                        'future' => false
                    ]
                ]
            );
        }
    }

    public function setProxy(Proxy $proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return Proxy|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * @return mixed|null
     */
    public function nextProxy()
    {
        $proxy = $this->getProxy();
        return $proxy? $proxy->nextProxy() : null;
    }


    /**
     * @param RequestInterface $request
     * @return mixed|HttpResponse
     */
    public function load(RequestInterface $request)
    {
        $headers = [];
        foreach ($request->getHeaders() as $key => $val) {
            $headers[$key] = implode(", ", $val);
        }
        $request = $this->client->createRequest(
            $request->getMethod()?: 'GET',
            $request->getUri(),
            [
                'headers' => $headers,
                'proxy' => $this->nextProxy()
            ]
        );
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $guzzleResponse = $e->getResponse();
        }
        $httpResponse = new HttpResponse('php://memory', $guzzleResponse->getStatusCode(), $guzzleResponse->getHeaders());
        $httpResponse->getBody()
            ->write($guzzleResponse->getBody()->getContents());
        return $httpResponse;
    }
}
