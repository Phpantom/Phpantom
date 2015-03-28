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
                            'protocols' => ['http', 'https'],
//                            'proxy'   => 'tcp://localhost:8888'
                        ],
                        'cookies' => true,
                        'stream' => false,
                        'future' => false
                    ]
                ]
            );
        }
    }

    /**
     * @param RequestInterface $request
     * @return mixed|HttpResponse
     */
    public function load(RequestInterface $request)
    {
        //@todo add proxy $request->getMeta('proxy')? $request->getMeta('proxy') : null
        $request = $this->client->createRequest(
            $request->getMethod(),
            $request->getUri(),
            [
                'headers' => $request->getHeaders()
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
