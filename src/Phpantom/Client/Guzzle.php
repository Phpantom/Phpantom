<?php

namespace Phpantom\Client;

use GuzzleHttp\Client;
use Phpantom\Resource;
use Phly\Http\Response;

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
     * @param \Phpantom\Resource $resource
     * @return mixed
     */
    public function load(Resource $resource)
    {
        //@todo add proxy $resource->getMeta('proxy')? $resource->getMeta('proxy') : null
        $request = $this->client->createRequest(
            $resource->getMethod(),
            $resource->getUri(),
            [
                'headers' => $resource->getHeaders()
            ]
        );
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $guzzleResponse = $e->getResponse();
        }
        $response = new Response('php://memory', $guzzleResponse->getStatusCode(), $guzzleResponse->getHeaders());
        $response->getBody()
            ->write($guzzleResponse->getBody()->getContents());
        return $response;
    }
}
