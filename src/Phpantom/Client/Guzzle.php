<?php

namespace Phpantom\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Zend\Diactoros\Response as HttpResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;

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

    private $requestsPerInstance = 10;

    private $requestsNumber = 0;

    private $config = [
        'defaults' => [
            'timeout' => 10,
            'allow_redirects' => [
                'max' => 5,
                'strict' => false,
                'referer' => true,
                'protocols' => ['http', 'https']
            ],
            'cookies' => true,
            'stream' => false,
            'future' => false
        ]
    ];

    /**
     * @param int $requestsPerInstance
     * @return $this
     */
    public function setRequestsPerInstance($requestsPerInstance)
    {
        $this->requestsPerInstance = $requestsPerInstance;
        return $this;
    }

    /**
     * @return int
     */
    public function getRequestsPerInstance()
    {
        return $this->requestsPerInstance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = $this->getInstance();
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
        return $proxy ? $proxy->nextProxy() : null;
    }


    protected function getClient()
    {
        if ($this->requestsNumber && (0 === $this->requestsNumber % $this->requestsPerInstance)) {
            $this->client = $this->getInstance();
        }
        return $this->client;
    }

    /**
     * @param RequestInterface $request
     * @return mixed|HttpResponse
     */
    public function load(RequestInterface $request)
    {
        $this->requestsNumber++;
        $headers = [];
        foreach ($request->getHeaders() as $key => $val) {
            $headers[$key] = implode(", ", $val);
        }
        $request = new Request(
            $request->getMethod() ? : 'GET',
            (string) $request->getUri(),
            [
                'headers' => $headers,
                'proxy' => (string) $this->nextProxy()
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

    protected function getInstance()
    {
        return new Client($this->getConfig());
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

}
