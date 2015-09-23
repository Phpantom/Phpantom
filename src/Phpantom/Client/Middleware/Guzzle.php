<?php

namespace Phpantom\Client\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Zend\Diactoros\Response as HttpResponse;
use Phpantom\Client\Proxy;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Relay\MiddlewareInterface;

/**
 * Class Guzzle
 * @package Phpantom\Client\Middleware
 */
class Guzzle implements MiddlewareInterface
{

    private $config = [
        'allow_redirects' => [
            'max' => 5,
            'protocols' => ['http', 'https'],
            'strict' => false,
            'referer' => true
        ],
        'http_errors' => true,
        'decode_content' => true,
        'verify' => true,
        'cookies' => true
    ];

    /**
     * @var Client
     */
    private $httpClient;
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @param Client $httpClient
     * @param Proxy $proxy
     */
    public function __construct(Client $httpClient = null , Proxy $proxy = null)
    {
        $this->httpClient = $httpClient;
        $this->proxy = $proxy;
    }

    public function setHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
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

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        if (!isset($this->httpClient)) {
            $this->httpClient = new Client($this->getConfig());
        }
        return $this->httpClient;
    }

    /**
     * @return Proxy
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

    /**
     * @param Request $request the request
     * @param Response $response the response
     * @param callable|MiddlewareInterface|null $next the next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $request = new \GuzzleHttp\Psr7\Request(
            $request->getMethod() ?: 'GET',
            (string)$request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );
        try {
            $guzzleResponse = $this->getHttpClient()->send($request, ['proxy' => $this->nextProxy()]);
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
        return is_null($next)? $httpResponse : $next($request, $httpResponse);
    }
}