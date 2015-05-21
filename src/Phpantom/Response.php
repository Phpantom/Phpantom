<?php
namespace Phpantom;

use Zend\Diactoros\Response as HttpResponse;

class Response
{
    /**
     * @var HttpResponse
     */
    private $httpResponse;

    public function __construct(HttpResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * @return mixed
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getContent()
    {
        return (string) $this->getHttpResponse()->getBody();
    }

    /**
     * Proxy
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        $response = call_user_func_array([$this->httpResponse, $method], $params);
        if (0 === strpos($method, 'with')) {
            $this->httpResponse = $response;
            return $this;
        }
        return $response;
    }

}
