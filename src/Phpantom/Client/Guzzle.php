<?php

namespace Phpantom\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
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
            $code = $guzzleResponse->getStatusCode();
            $headers = $guzzleResponse->getHeaders();
            $contents = $guzzleResponse->getBody()->getContents();
        } catch (TransferException $e) {
            if (is_callable([$e, 'getResponse']) && ($guzzleResponse = $e->getResponse())) {
                $code = $guzzleResponse->getStatusCode();
                $headers = $guzzleResponse->getHeaders();
                $contents = $guzzleResponse->getBody()->getContents();
            } else {
                $code = '500';
                $headers = [];
                $contents = '';
            }
        }

        $httpResponse = new HttpResponse('php://memory', $code, $headers);
        $httpResponse->getBody()
            ->write($contents);
        return $httpResponse;
    }
}
